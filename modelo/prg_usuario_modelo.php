<?php
class prg_usuario_model{
    private $db;
    private $usuarios;
	private $listas;
    public function __construct(){
        $this->db=new DBManejador();
        $this->usuarios=array();
		$this->listas=array();
    }
    /* MODELO para seleccionar  usuarios
        mayo 2020
		Autor: Enrique Bazalar alephsystem@gmail.com
    */
	
	// login de pagina inicio
    public function login($login,$password,$id_pais){
        $password=md5($password);
        $sql="SELECT id_usuario, a.id_auditor,nombres, usuario,clave, tipo , id_pais,id_rol,foto, u.azuread
				FROM prg_usuarios u INNER JOIN prg_auditor a ON u.id_auditor=a.id_auditor 
				WHERE u.flag='1' AND a.flgstatus='1' and a.flag='1' and usuario='$login' and contrasena='$password' and id_pais='$id_pais'";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;

    }
	
	public function faltandiasclave($id_auditor){
		 $sql="SELECT to_days(fechaclave) - to_days(now()) + 60 as dias 
		 from prg_usuarios where id_auditor=$id_auditor ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	
	public function tipohomeauditor($id_auditor){
		 $sql="SELECT GROUP_CONCAT(DISTINCT tipohome) AS tipo 
				FROM prg_auditorxrol INNER JOIN prg_roles ON prg_auditorxrol.id_rol=prg_roles.id_rol
				WHERE id_auditor=$id_auditor ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	
	
	// login de pagina inicio
    public function login_azuread($correo,$id_pais=null){
        $sql="SELECT id_usuario, a.id_auditor,nombres, usuario,clave, tipo , id_pais,id_rol,foto
				FROM prg_usuarios u INNER JOIN prg_auditor a ON u.id_auditor=a.id_auditor 
				WHERE u.flag='1' AND a.flag='1' and u.azuread='$correo' ";
		if(!empty($id_pais))
			$sql.=" and a.id_pais='$id_pais'";
		$sql.="	limit 0,1";
		
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
    }
	
	 public function quepais($correo){
		unset($this->usuarios);
        $sql="SELECT distinct p.nombre, p.id_pais
				FROM prg_usuarios u INNER JOIN prg_auditor a ON u.id_auditor=a.id_auditor 
					inner join prg_paises p on a.id_pais=p.id_pais
				WHERE u.flag='1' AND a.flag='1' and u.azuread='$correo'
				order by 1 ";
						
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->usuarios[]=$filas;
        }
        return $this->usuarios;	
    }
	
	public function enlacesPrimary($id_rol=null,$id_pais){
		unset($this->usuarios);
		if(!empty($id_rol))
			$sql="SELECT DISTINCT m.id_menu,IFNULL(p.nombre,m.nombre ) AS nombre, 
					CASE m.nombre 
					WHEN 'Mantenimiento' THEN 'fa-database'
					WHEN 'Operaciones' THEN 'fa-truck'
					WHEN 'Reportes' THEN 'fa-file-excel'
					WHEN 'Calendario' THEN 'fa-calendar'
					WHEN 'Asistentes' THEN 'fa-user-circle'
					WHEN 'Listas Integradas' THEN 'fa-address-card'
					ELSE 'fa-folder-open'
				END AS icono
					FROM prg_menus  m INNER JOIN prg_enlaces e ON m.id_menu=e.id_menu 
						INNER JOIN prg_roles_enlaces r ON e.id_enlace=r.id_enlace
						LEFT JOIN prg_menu_pais p ON m.id_menu=p.id_menu AND p.id_pais='$id_pais'
					WHERE m.flag='1' AND m.id_pais='esp' AND e.flag='1' AND id_rol=$id_rol";
		else
			$sql="SELECT DISTINCT m.id_menu,m.nombre ,'fa-folder-open' as icono
				FROM prg_menus  m  
				WHERE m.flag='1' ";
		$sql.=" order by 2";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->usuarios[]=$filas;
        }
        return $this->usuarios;	
		
	}
	
	public function enlacesPrimary_new($id_auditor=null,$id_pais){
		unset($this->usuarios);
		if(!empty($id_auditor)){
			$sql="SELECT DISTINCT m.id_menu,IFNULL(p.nombre,m.nombre ) AS nombre, 
					CASE m.nombre
					WHEN 'Mantenimiento' THEN 'fa-database'
					WHEN 'Operaciones' THEN 'fa-truck'
					WHEN 'Reportes' THEN 'fa-file-excel'
					WHEN 'Calendario' THEN 'fa-calendar'
					WHEN 'Asistentes' THEN 'fa-user-circle'
					WHEN 'Listas Integradas' THEN 'fa-address-card'
					ELSE 'fa-folder-open'
				END AS icono
					FROM prg_menus  m 
						INNER JOIN prg_enlaces e ON m.id_menu=e.id_menu 
						INNER JOIN prg_roles_enlaces r ON e.id_enlace=r.id_enlace AND r.id_pais='$id_pais'
						LEFT JOIN prg_menu_pais p ON m.id_menu=p.id_menu AND p.id_pais='$id_pais'
					WHERE m.flag='1' AND m.id_pais='esp' 
						AND e.id_enlace NOT IN (SELECT id_enlace FROM prg_enlace_inactivo WHERE id_pais='$id_pais')  ";
	
				$sql.="		AND e.flag='1' AND id_rol in (
						select id_rol from prg_auditorxrol where id_auditor=$id_auditor
					)";
		}else
			$sql="SELECT DISTINCT m.id_menu,m.nombre ,'fa-folder-open' as icono
				FROM prg_menus  m
				WHERE m.flag='1' ";
		$sql.=" order by 2";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->usuarios[]=$filas;
        }
        return $this->usuarios;	
		
	}
	
	public function enlacesSecundar($id_rol,$id_pais){
		
		/*
		SUBSTRING_INDEX(SUBSTRING_INDEX(REPLACE(REPLACE(url,'../../cuperu2',''),'?id=','') , '/', 2), '/', -1) AS control,
		REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(REPLACE(REPLACE(REPLACE(url,'/?','/index?'),'../../cuperu2',''),'?id=','') , '/', 3), '/', -1),'.php','') AS accion
		*/
		
		unset($this->listas);
		$sql="SELECT DISTINCT e.id_enlace,
				IFNULL(prg_enlace_pais.nombre,e.nombre) AS nombre,
				e.url, e.id_menu,
				controlador AS control,
				accion,
				ifnull(icono,'fa-minus-square') as icono
			FROM prg_enlaces e 
			INNER JOIN prg_roles_enlaces r ON e.id_enlace=r.id_enlace 
					LEFT JOIN prg_enlace_pais ON e.id_enlace=prg_enlace_pais.id_enlace
						AND prg_enlace_pais.id_pais='$id_pais'
			WHERE e.flag='1' AND e.id_pais='esp' AND id_rol=$id_rol and controlador!=''
			order by 2";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function enlacesSecundar_new($id_auditor,$id_pais){
		unset($this->listas);
		$sql="SELECT DISTINCT e.id_enlace,
				IFNULL(prg_enlace_pais.nombre,e.nombre) AS nombre,
				e.url, e.id_menu,
				controlador AS control,
				accion,
				ifnull(icono,'fa-minus-square') as icono
			FROM prg_enlaces e 
			INNER JOIN prg_roles_enlaces r ON e.id_enlace=r.id_enlace  AND r.id_pais='$id_pais'
					LEFT JOIN prg_enlace_pais ON e.id_enlace=prg_enlace_pais.id_enlace
						AND prg_enlace_pais.id_pais='$id_pais'
				WHERE e.flag='1' AND e.id_pais='esp' and controlador!='' ";

			if($id_pais!='eng')
						$sql.=" and e.id_enlace!=1207 ";
			if($id_pais!='esp')
						$sql.=" and e.id_enlace!=1212 ";
					
			$sql.=" AND id_rol  in (
						select id_rol from prg_auditorxrol where id_auditor=$id_auditor
					) 
			order by 2";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

	
    public function getCentroCostoUser($codusuario){
		$sql="select Users.*, ifnull(id_centrocosto,'') as centrocosto
			from Users
			where Users.Id=$codusuario";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	
	// seleccionar un usuario
    public function OneUsuario($codusuario){
        
        $sql="select * from Users where Id=$codusuario ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
    }
	
	public function OneUsuariobypersona($idpersona){
        
        $sql="select * from Users where idpersona=$idpersona ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
    }

	// index usuario
	
	public function selec_total_usuario($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM Users 
			WHERE flag = '1' ";
			
		if(!empty($searchQuery)) $sql.=" $searchQuery ";
		
		$consulta=$this->db->consultarOne($sql);
		
        return $consulta;	
	}
	
	
	public function select_usuarios($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
       unset($this->usuarios);
        $sql="SELECT
			  Users.id AS id_user,
			  COALESCE(Users.username,'') AS username ,
			  replaceCarEspecial(IFNULL(Users.realname,'')) AS realname,
			  Users.password AS contra,
			  Users.passwordtype AS passwordtype,
			  Users.realm AS realm,
			  COALESCE(Users.email,'') AS email,
			  Users.idpersona AS idpersona,
			  Users.foto AS foto,
			  IFNULL(isAdministracion,'0') AS isAdministracion,
			  IFNULL(isContabilidad,'0') AS isContabilidad,
			  IFNULL(isInspector,'0') AS isInspector,
			  IFNULL(id_centrocosto,'') AS id_centrocosto,
			  CASE isInspector WHEN '1' THEN 'Inspector' ELSE '' END AS dscinspector,
			  CASE isContabilidad WHEN '1' THEN 'Contabilidad' ELSE '' END AS dscconta,
			  CASE isAdministracion WHEN '1' THEN 'Administrador' ELSE '' END AS dscadmin,
			  ciudad,
			  IFNULL(telefono,'') AS telefono,
			  IFNULL(costo_almacen,'') AS costo_almacen,
			  IFNULL(costo_puerto,'') AS costo_puerto,
			  IFNULL(costo_normal,'') AS costo_normal,
			  IFNULL(firma,'') AS firma,
			  IFNULL(isFacturaSurvey,'0') AS isFacturaSurvey,
			  IFNULL(flgrolSurvey,'0') AS flgrolSurvey,
			  IFNULL(flduplicaos,'0') AS flduplicaos,
			  IFNULL(flgcorreoOSsOF,'0') AS flgcorreoOSsOF

		FROM 
			  Users 
		WHERE  flag='1'  $searchQuery ";
			
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->usuarios[]=$filas;
        }
        return $this->usuarios;	
    }	
	
	public function select_ciudad(){
		unset($this->usuarios);
		$sql=" select * from mae_ciudad where flag='1' ";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->usuarios[]=$filas;
        }
        return $this->usuarios;	
	}
	
	public function select_rol(){
		unset($this->usuarios);
		$sql=" select id_rol, nombre 
				from prg_roles_survey
				where flag='1'
				order by nombre";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->usuarios[]=$filas;
        }
        return $this->usuarios;	
	}
	
	public function select_enlacesxuser($codusuario=null){
		unset($this->usuarios);
		$sql=" select id_rol, nombre 
				from prg_roles_survey
				where flag='1'
				order by nombre";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->usuarios[]=$filas;
        }
        return $this->usuarios;	
	}
	
	// insert usuario
    public function insert_usuario($id_centrocosto,$idpersona,$nombre,$email,$telefono,$isContabilidad,$isAdministracion,$flgcorreoOSsOF,$isInspector,$flgrolSurvey,$costo_normal,$costo_almacen,$costo_puerto,$ciudad,$isFacturaSurvey,$flduplicaos,$username,$contra,$usuario,$ip){
       $id_centrocosto = !empty($id_centrocosto) ? "'$id_centrocosto'" : "NULL";
	   $costo_normal = !empty($costo_normal) ? "'$costo_normal'" : "NULL";
	   $costo_almacen = !empty($costo_almacen) ? "'$costo_almacen'" : "NULL";
	   $costo_puerto = !empty($costo_puerto) ? "'$costo_puerto'" : "NULL";
   
        $sql="INSERT INTO Users(
				passwordtype,realm,	domains,idpersona,username,	Password,realname,id_centrocosto,isAdministracion,
				isContabilidad,	isInspector,ciudad,isshow,	email,telefono,costo_normal,costo_almacen,
				costo_puerto,isFacturaSurvey,flgrolSurvey ,	flduplicaos,flgcorreoOSsOF
			)
			VALUES(
				2,
				'*',
				'',
				$idpersona,
				'$username',
				'$contra',
				'$nombre',
				$id_centrocosto,
			   '$isAdministracion',
			   '$isContabilidad',
			   '$isInspector',
			   '$ciudad',
			   '0',
			   '$email',
			   '$telefono',
			    $costo_normal,
			    $costo_almacen,
			    $costo_puerto,
			   '$isFacturaSurvey',
			   '$flgrolSurvey',
			   '$flduplicaos',
			   '$flgcorreoOSsOF'
			)";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_usuario($codusuario,$id_centrocosto,$idpersona,$nombre,$email,$telefono,$isContabilidad,$isAdministracion,$flgcorreoOSsOF,$isInspector,$flgrolSurvey,$costo_normal,$costo_almacen,$costo_puerto,$ciudad,$isFacturaSurvey,$flduplicaos,$username,$contra,$usuario,$ip){
       $id_centrocosto = !empty($id_centrocosto) ? "'$id_centrocosto'" : "NULL";
	   $costo_normal = !empty($costo_normal) ? "'$costo_normal'" : "NULL";
	   $costo_almacen = !empty($costo_almacen) ? "'$costo_almacen'" : "NULL";
	   $costo_puerto = !empty($costo_puerto) ? "'$costo_puerto'" : "NULL";
	   
        $sql="update Users
				set 
					idpersona=$idpersona,
					username='$username',
					realname='$nombre',
					id_centrocosto=$id_centrocosto,
					isAdministracion='$isAdministracion',
					isContabilidad='$isContabilidad',
					isInspector='$isInspector',
					ciudad='$ciudad',
					email='$email',
					telefono='$telefono',
					costo_normal=$costo_normal,
					costo_almacen=$costo_almacen,
					costo_puerto=$costo_puerto,
					isFacturaSurvey='$isFacturaSurvey',
					flgrolSurvey ='$flgrolSurvey',
					flduplicaos='$flduplicaos',
					flgcorreoOSsOF='$flgcorreoOSsOF',";
			if($password!='')	$sql.="	Password='$contra',";
			$sql.="	flag='1'
				where Id=$codusuario";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	public function delete_enlacesxusuario($codusuario){
		 $sql="delete from enlacesxusuario
				where codusuario=$codusuario";
		$consulta=$this->db->execute($sql);
        return $consulta;
	}
	
	public function insert_enlacesxusuario($codusuario,$codenlace){
		 $sql="INSERT INTO enlacesxusuario(codusuario,codenlace) 
				VALUES( $codusuario, $codenlace )";
		$consulta=$this->db->execute($sql);
        return $consulta;
	}
	
	// delete usuario
	public function delete_usuario($codusuario){
       
        $sql="update Users
				set flag='0'
				where Id=$codusuario";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

}
?>