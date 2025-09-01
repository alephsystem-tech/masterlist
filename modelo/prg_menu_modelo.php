<?php
class prg_menu_model{
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

	public function select_menubypais($id_pais){
		unset($this->listas);
		$sql="SELECT * from prg_menu_pais where id_pais='$id_pais' order by nombre";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_menu($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_pais){
		unset($this->listas);
		$sql="SELECT m.id_menu , IFNULL(p.nombre,m.nombre ) AS nombre,  
			group_concat(ifnull(ep.nombre,e.nombre) separator '<br>') as enlace
			from prg_menus m left join prg_enlaces e on m.id_menu=e.id_menu and e.flag='1'
				LEFT JOIN prg_menu_pais p ON m.id_menu=p.id_menu AND p.id_pais='$id_pais'
				LEFT JOIN prg_enlace_pais ep ON e.id_enlace=ep.id_enlace AND ep.id_pais='$id_pais'
			where m.flag='1'  $searchQuery ";
		$sql.=" group by m.id_menu";	
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_menu($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_menus m
        WHERE flag='1' $searchQuery " ;
		
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_menu($id_menu){
		
		$sql="SELECT IFNULL(p.nombre,m.nombre ) AS nombre, m.id_menu
			from prg_menus m LEFT JOIN prg_menu_pais p ON m.id_menu=p.id_menu AND p.id_pais='eng'
			where m.id_menu=$id_menu ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_menu($nombre,$id_pais,$usuario,$ip){

        $sql="insert into prg_menus(nombre,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$nombre','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$id_menu=$this->db->executeIns($sql);
		
		$sql="insert into prg_menu_pais(nombre,id_pais,id_menu)  values('$nombre','$id_pais',$id_menu)";
		$this->db->execute($sql);
		
        return $id_menu;
    }	

	// update usuario
    public function update_menu($id_menu,$nombre,$id_pais,$usuario,$ip){
	   
        $sql="update prg_menus 
				set nombre='$nombre' ,
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_menu=$id_menu and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
		
		$sql="select ifnull(count(*),0) as total from prg_menu_pais where id_menu=$id_menu and id_pais='$id_pais'";
		$consulta=$this->db->consultarOne($sql);
		if($consulta['total']>0)
			$sql="update prg_menu_pais set nombre='$nombre'  where id_menu=$id_menu and id_pais='$id_pais'";
		
		else
			$sql="insert into prg_menu_pais(nombre,id_pais,id_menu)  values('$nombre','$id_pais',$id_menu)";	
		
		$consulta=$this->db->execute($sql);
		
        return $consulta;
    }	

    public function delete_menu($id_menu){
	   
        $sql="update prg_menus set flag='0' where id_menu=$id_menu";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

}
?>