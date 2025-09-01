<?php
class prg_estadoactividad_model{
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

	public function select_estadoactividad($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT * from prg_estadoactividad where flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_estadoactividad_select($id_pais){
		unset($this->listas);
		$sql="SELECT * FROM prg_estadoactividad WHERE flag='1' and id_estadoactividad in (
				select id_estadoactividad from prg_estadoactividadxpais where id_pais='$id_pais'
				) 
				ORDER BY descripcion" ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	public function select_one_estadoactividad($id_estadoactividad){
		unset($this->listas);
		$sql="SELECT * from prg_estadoactividad where id_estadoactividad=$id_estadoactividad ";
	
		$consulta=$this->db->consultarOne($sql);		
		return $consulta;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_estadoactividad($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_estadoactividad
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	
	// total de registros por auditor fecha
	public function update_estadoIcono($id_estadoactividad,$imagen){
		$sql=" update prg_estadoactividad set imagen='$imagen'
			WHERE id_estadoactividad=$id_estadoactividad " ;
		$consulta=$this->db->execute($sql);
		return $consulta;	
	}
	
	public function select_estadoactividadxpais($id_estadoactividad){
		unset($this->listas);
		$sql="SELECT prg_paises.*,  IFNULL(prg_estadoactividadxpais.id_pais,'') AS codrelacion
			FROM prg_paises LEFT JOIN prg_estadoactividadxpais ON prg_paises.id_pais = prg_estadoactividadxpais.id_pais 
			AND prg_estadoactividadxpais.id_estadoactividad=$id_estadoactividad
			where prg_paises.flag='1' order by nombre" ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	public function delete_estadoxpais($id_estadoactividad){
		$sql=" delete from prg_estadoactividadxpais where id_estadoactividad=$id_estadoactividad";
		$consulta=$this->db->execute($sql);
		return $consulta;
	}
	
	public function insert_estadoxpais($sql){
		$sql=" insert into prg_estadoactividadxpais(id_estadoactividad,id_pais) values " . $sql;
		
		$consulta=$this->db->execute($sql);
		return $consulta;
	}

}
?>