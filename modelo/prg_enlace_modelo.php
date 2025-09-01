<?php
class prg_enlace_model{
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

	public function select_enlace($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_pais){
		unset($this->listas);
		$sql="SELECT prg_enlaces.id_enlace, 
				case when ifnull(i.id_enlace,0)>0 then '0' else '1' end  as flgactivo,
				IFNULL(prg_enlace_pais.nombre,prg_enlaces.nombre ) AS nombre,
				prg_enlaces.id_menu,prg_enlaces.url,prg_enlaces.accion,prg_enlaces.controlador, 
				CASE 
					WHEN IFNULL(p.nombre,'')!='' THEN p.nombre
					WHEN IFNULL(prg_menus.nombre,'')!='' THEN prg_menus.nombre
					WHEN IFNULL(s.nombre,'')!='' THEN s.nombre
					ELSE ''
				END AS menu
				from prg_enlaces left join prg_menus on prg_enlaces.id_menu=prg_menus.id_menu
					LEFT JOIN prg_enlace_pais ON prg_enlaces.id_enlace=prg_enlace_pais.id_enlace
					AND prg_enlace_pais.id_pais='$id_pais'
					LEFT JOIN prg_menu_pais p ON prg_menus.id_menu=p.id_menu AND p.id_pais='$id_pais'
					left join  prg_enlaces s on prg_enlaces.id_menu=s.id_enlace
					left join prg_enlace_inactivo i on prg_enlaces.id_enlace=i.id_enlace and i.id_pais='$id_pais' 
				where prg_enlaces.flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_enlace($searchQuery=null){
		$sql=" SELECT COUNT(distinct prg_enlaces.id_enlace) AS total 
			FROM    prg_enlaces 
					left join prg_menus on prg_enlaces.id_menu=prg_menus.id_menu
					LEFT JOIN prg_enlace_pais ON prg_enlaces.id_enlace=prg_enlace_pais.id_enlace
					
			WHERE prg_enlaces.flag='1' $searchQuery " ;
		
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_enlace($id_enlace,$id_pais){
		
		$sql="SELECT prg_enlaces.id_enlace,
				IFNULL(prg_enlace_pais.nombre,prg_enlaces.nombre) AS nombre, 
				id_menu,url,accion,controlador
			FROM prg_enlaces LEFT JOIN prg_enlace_pais ON prg_enlaces.id_enlace=prg_enlace_pais.id_enlace
				AND prg_enlace_pais.id_pais='$id_pais'
			WHERE prg_enlaces.id_enlace=$id_enlace ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_one_enlace_bycontrol($id_pais,$controlador,$accion){
		
		$sql="SELECT *
			FROM prg_enlaces 
			WHERE id_pais='$id_pais' and controlador='$controlador' and accion='$accion' ";
		$consulta=$this->db->consultarOne($sql);
		
        return $consulta;	
		
	}
	
	public function select_enlacemenu($id_pais){
		$this->listas=null;
		$sql="SELECT *
			FROM prg_enlaces 
			WHERE id_pais='$id_pais' AND IFNULL(accion,'')='' AND flag='1' AND url='' ";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	 public function insert_enlace($id_menu,$nombre,$accion,$controlador,$id_pais,$usuario,$ip){

        $sql="insert into prg_enlaces(id_menu,nombre,accion,controlador,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values($id_menu,'$nombre','$accion','$controlador','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	 public function insert_enlace_pais($id_enlace,$nombre,$id_pais){

        $sql="insert into prg_enlace_pais(id_enlace,nombre,id_pais)
        values($id_enlace,'$nombre','$id_pais')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	
	
	// update usuario
    public function update_enlace($id_menu,$id_enlace,$nombre,$accion,$controlador,$id_pais,$usuario,$ip){
	   
        $sql="update prg_enlaces 
				set nombre='$nombre' ,accion='$accion' ,controlador='$controlador' ,id_menu='$id_menu' ,
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_enlace=$id_enlace and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	public function select_enlace_pais($id_enlace,$id_pais){
		$sql="select count(*) as total from prg_enlace_pais where id_enlace=$id_enlace and id_pais='$id_pais'";
		$consulta=$this->db->consultarOne($sql);
		
        return $consulta;
	}

    public function update_enlace_pais($id_enlace,$nombre,$id_pais){
	   
        $sql="update prg_enlace_pais
				set nombre='$nombre'
                where id_enlace=$id_enlace and id_pais='$id_pais'";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
    public function delete_enlace($id_enlace){
	   
        $sql="update prg_enlaces set flag='0' where id_enlace=$id_enlace";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	public function activa_enlace($id_enlace,$flgactivo,$id_pais){
		if($flgactivo=='1'){
			$sql="delete from  prg_enlace_inactivo where id_enlace=$id_enlace and id_pais='$id_pais'";
		}else{
			$sql="insert into prg_enlace_inactivo(id_enlace,id_pais) values ($id_enlace,'$id_pais')";
		}
		echo $sql;
		
		$consulta=$this->db->execute($sql);
        return $consulta;
	 }

}
?>