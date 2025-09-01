<?php
class maestropersona_model{
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
	
		// select * data cabecera orden de pago 26072020
	public function selec_total_persona($searchQuery=null){
		$sql=" SELECT COUNT(*) as total FROM
				(
					SELECT maestropersona.idpersona AS total
					FROM maestropersona INNER JOIN t_entidades ON maestropersona.idpersona=t_entidades.idpersona 
						INNER JOIN t_tipoentidad ON t_entidades.id_tipoentidad=t_tipoentidad.id_tipoentidad 
					WHERE maestropersona.flag='1' AND t_entidades.flag='1' 
						and t_tipoentidad.id_tipoentidad not in ('V','B') ";
				if(!empty($searchQuery)) $sql.=" $searchQuery ";				
				$sql.="	GROUP BY maestropersona.idpersona 
				) AS vista";
		$consulta=$this->db->consultarOne($sql);
		
        return $consulta;	
	}
	
    public function selec_personasAx($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		$sql="SELECT maestropersona.idpersona, 
				CONCAT_WS(' ',nombre,apepaterno,apematerno) AS nombres,
				IFNULL(razon_social,'') AS razon_social,
				ruc,dni,   
				TRIM(CASE WHEN IFNULL(razon_social,'')!='' THEN razon_social
				ELSE CONCAT_WS(' ',nombre,apepaterno,apematerno)
				END) AS fullname,
				email,
				GROUP_CONCAT(t_tipoentidad.descripcion) AS entidad,
				CASE idtipopersona WHEN 1 THEN 'Natural' ELSE 'Juridica' END AS tipo
			FROM maestropersona INNER JOIN 
				t_entidades ON maestropersona.idpersona=t_entidades.idpersona INNER JOIN
				t_tipoentidad ON t_entidades.id_tipoentidad=t_tipoentidad.id_tipoentidad
			WHERE 	maestropersona.flag='1' AND t_entidades.flag='1' 
				and t_tipoentidad.id_tipoentidad not in ('V','B') 
				$searchQuery ";
		$sql.=" 
			GROUP BY maestropersona.idpersona
			order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;		
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;
		
	}
	
	
	public function select_persona($tipo,$search=null){
		$search=strtoupper($search);
		$sql="SELECT maestropersona.idpersona, 
			CONCAT_WS(' ',nombre,apepaterno,apematerno) AS nombres,
			razon_social,ruc,dni,   
			trim(CASE WHEN IFNULL(razon_social,'')!='' THEN razon_social
			WHEN IFNULL(apepaterno,'')!='' THEN CONCAT_WS(' ',nombre,apepaterno,apematerno)
			ELSE ''
			END) AS fullname
		FROM 
			t_entidades INNER JOIN
			maestropersona ON t_entidades.idpersona=maestropersona.idpersona
		WHERE t_entidades.flag='1' AND	maestropersona.flag='1'";
		if(!empty($search)) 
			$sql.="	 and upper(CASE WHEN IFNULL(razon_social,'')!='' THEN razon_social
			WHEN IFNULL(apepaterno,'')!='' THEN CONCAT_WS(' ',nombre,apepaterno,apematerno)
			ELSE ''
			END) like '%$search%'";
		if($tipo!='X') $sql.="	 AND id_tipoentidad='$tipo' ";
		else $sql.=" and maestropersona.idpersona not in (select idpersona from Users where flag='1')"; 
		$sql.=" ORDER BY fullname ";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->usuarios[]=$filas;
        }
        return $this->usuarios;	
		
	}
	
	public function select_personaorigen($idpersona){
		$sql="SELECT maestropersona.idpersona, 
			CONCAT_WS(' ',nombre,apepaterno,apematerno) AS nombres,
			razon_social,ruc,dni,   
			TRIM(CONVERT(CASE      WHEN IFNULL(razon_social,'')!='' THEN razon_social
			WHEN IFNULL(apepaterno,'')!='' THEN CONCAT_WS(' ',nombre,apepaterno,apematerno)
			ELSE ''
			END,CHAR)) AS fullname
		FROM 
			t_entidades INNER JOIN
			maestropersona ON t_entidades.idpersonafactura=maestropersona.idpersona
		WHERE
			t_entidades.flag='1' AND
			maestropersona.flag='1'
			AND t_entidades.idpersona=$idpersona";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->usuarios[]=$filas;
        }
        return $this->usuarios;	
		
	}

	// seleccionar un usuario
    public function select_one_persona($idpersona){
        
        $sql="select *,
			CONVERT(CASE  WHEN IFNULL(razon_social,'')!='' THEN razon_social
			WHEN IFNULL(apepaterno,'')!='' THEN CONCAT_WS(' ',nombre,apepaterno,apematerno)
			ELSE ''	END,CHAR) AS fullname
			from maestropersona where idpersona=$idpersona ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
    }
	
	function delete_one_persona($idpersona,$usuario,$ip){
		 $sql="update maestropersona
				set flag='0'
				where idpersona=$idpersona";
		$consulta=$this->db->execute($sql);
        return $consulta;
		
	}
	
		// seleccionar un usuario
    public function select_oneDireccion_persona($idpersona){
        
        $sql="select 
				id_direccion,
				(case tipo
					when 'F' then 'Facturacion'
					when 'P' then 'Principal'
					when 'A' then 'Alternativo'
				end) as desctipo,
				ifnull(t_direcciones.id_pais,'') as id_pais,
				ifnull(t_mae_pais.nombre,'') as nombre,
				ifnull(ubi_id,'') as ubi_id,
				ifnull(dircalle1,'') as dircalle1,
				ifnull(dirciudad1,'') as dirciudad1,
				ifnull(direstado1,'') as direstado1,
				ifnull(telefono,'') as telefono,
				ifnull(fax,'') as fax,
				ifnull(telfax,'') as telfax,
				ifnull(celular,'') as celular,
				ifnull(tipo,'') as tipo
			from 
				t_direcciones left join
				t_mae_pais on t_direcciones.id_pais=t_mae_pais.id_pais
			where
				idpersona=$idpersona and
				t_direcciones.flag='1'";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
    }
	
	
	// select tipo de usuario.editar y nuevo
	public function select_vendedorOF(){
		unset($this->listas);
		$sql="select codvendedor,vendedor from prg_vendedor where flag='1' and (modulo='EX' or modulo is null)";
		$consulta=$this->db->consultar($sql);
		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function select_tipoentidad(){
		unset($this->listas);
		$sql="select id_tipoentidad ,descripcion
			from t_tipoentidad 
			where flag='1' 
			order by orden ";
		$consulta=$this->db->consultar($sql);
		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_tipoentidad_persona($idpersona){
		unset($this->listas);
		$sql="select group_concat(id_tipoentidad) as tipoentidad
			from t_entidades
			where flag='1' and idpersona=$idpersona";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
		
	}
	
	function update_persona($idpersona,$idtipopersona,$ruc,$razon_social,$dni,$nombre,$apepaterno,$apematerno,$email,$observacion,$paginaweb,$usuario,$ip){
		 $sql="update maestropersona
				set 
					ruc='$ruc',
					idtipopersona='$idtipopersona',
					razon_social='$razon_social',
					nombre='$nombre',
					apepaterno='$apepaterno',
					apematerno='$apematerno',
					email='$email',
					obs='$observacion',
					paginaweb='$paginaweb'
				where idpersona=$idpersona";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
	}

	function insert_persona($idtipopersona,$ruc,$razon_social,$dni,$nombre,$apepaterno,$apematerno,$email,$observacion,$paginaweb,$usuario,$ip){
		 $sql="insert into maestropersona(
				ruc,idtipopersona,razon_social,nombre,apepaterno,apematerno,email,
				obs,paginaweb,flag)
				values (
				'$ruc','$idtipopersona','$razon_social','$nombre','$apepaterno','$apematerno','$email',
				'$observacion','$paginaweb','1')";
		$idpersona=$this->db->executeIns($sql);
        return $idpersona;
	}	
	
	
	function delete_personaentidad($idpersona,$usuario,$ip){
		$sql="delete from  t_entidades	where idpersona=$idpersona";
		$consulta=$this->db->execute($sql);
        return $consulta;
	}

	function insert_personaentidad($idpersona,$id_tipoentidad,$usuario,$ip){
		$sql="insert into t_entidades(idpersona,id_tipoentidad,flag,usuario_ingreso,fecha_ingreso,
				ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
			values ($idpersona,'$id_tipoentidad','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
		$consulta=$this->db->execute($sql);
        return $consulta;
	}
	
	public function getNameContactoPersona($idpersona){
		$sql="select 
				idpersona,
				id_tipoentidad,
				t_mae_contactos.id_contacto,
				nombre,
				apepaterno,
				convert(concat_ws(' ',nombre,apepaterno,apematerno),char) as fullname,
				ifnull(apematerno,'') as apematerno,
				ifnull(nrodocumento,'') as nrodocumento,
				ifnull(direccion,'') as direccion,
				ifnull(id_pais,'') as id_pais,
				ifnull(id_cargo,'') as id_cargo,
				ifnull(email,'') as email,
				ifnull(fono1,'') as fono1,
				ifnull(fono2,'') as fono2,
				ifnull(anexo,'') as anexo
			from 
				t_clientecontacto inner join 
				t_mae_contactos on t_clientecontacto.id_contacto= t_mae_contactos.id_contacto
			where 
			   t_clientecontacto.idpersona=$idpersona and
			   t_clientecontacto.flag='1' and
			   t_mae_contactos.flag='1' limit 0,1";

		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}


}
?>