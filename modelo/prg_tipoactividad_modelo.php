<?php
class prg_tipoactividad_model{
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

	public function select_tipoactividad($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *, case is_enviar_email when '1' then 'Si' else 'No' end as enviar_email,flgactivo,
				case flgobligacal when '1' then 'Si' else 'No' end as obligacal
				from prg_tipoactividad where flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_cat_tipoactividad(){
		unset($this->listas);
		$sql="SELECT * from prg_cat_tipoactividad where flag='1' order by categoria ";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	
	public function select_tipoactividad_select($id_pais,$id_tipoactividad=0,$flgactivo=null){
		unset($this->listas);
		$sql="SELECT descripcion,id_tipoactividad
			FROM prg_tipoactividad 
			WHERE flag='1' AND id_pais='$id_pais' ";
		
		if(!empty($flgactivo) and $id_tipoactividad>0)	
			$sql.="	and (flgactivo='$flgactivo' or  id_tipoactividad=$id_tipoactividad )";
		
		elseif(!empty($flgactivo))	
			$sql.="	and flgactivo='$flgactivo' ";
	
		$sql.="	order by descripcion";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_tipoactividad($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            prg_tipoactividad
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_tipoactividad($id_tipoactividad){
		
		$sql="SELECT *,case is_enviar_email when '1' then 'Si' else 'No' end as enviar_email,
				case flgobligacal when '1' then 'Si' else 'No' end as obligacal
				from prg_tipoactividad where id_tipoactividad=$id_tipoactividad ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_tipoactividad($descripcion,$detalle,$is_enviar_email,$id_categoria,$flgobligacal,$id_pais,$usuario,$ip){

        $sql="insert into prg_tipoactividad(descripcion,detalle,is_enviar_email,flgobligacal,id_categoria,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$descripcion','$detalle','$is_enviar_email','$flgobligacal','$id_categoria','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_tipoactividad($id_tipoactividad,$descripcion,$detalle,$is_enviar_email,$id_categoria,$flgobligacal,$id_pais,$usuario,$ip){
	   
        $sql="update prg_tipoactividad 
				set descripcion='$descripcion',detalle='$detalle',is_enviar_email='$is_enviar_email',
					id_categoria='$id_categoria',
					flgobligacal='$flgobligacal',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where id_tipoactividad=$id_tipoactividad ";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_tipoactividad($id_tipoactividad){
	   
        $sql="update prg_tipoactividad set flag='0' where id_tipoactividad=$id_tipoactividad";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

	 public function activa_actividad($id_tipoactividad,$flgactivo){
		 
		$sql="update prg_tipoactividad set flgactivo='$flgactivo' where id_tipoactividad=$id_tipoactividad";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
	 }
}
?>