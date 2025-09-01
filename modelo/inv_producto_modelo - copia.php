<?php
class inv_producto_model{
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
	
	public function selec_tipodsc(){
		unset($this->listas);
		$sql="SELECT codtipo, tipodsc FROM inv_tipo WHERE flag='1' order by tipodsc";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function selec_marca(){
		unset($this->listas);
		$sql="SELECT codmarca, marca FROM inv_marca WHERE flag='1' order by marca";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	

	public function select_producto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT inv_producto.*, 
				ifnull(inv_producto.descripcion,'') as descripcion,
				ifnull(inv_producto.modelo,'') as modelo,
				ifnull(inv_producto.serie,'') as serie,
				marca, tipodsc, IFNULL(coddestino,'') coddestino,IFNULL(codtransaccion,'') codtransaccion,
				IFNULL(inv_transaccion.usuariodestino,'[No asignado]') AS usuariodestino
				FROM inv_producto left JOIN inv_marca ON  inv_producto.codmarca=inv_marca.codmarca
					left JOIN inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo
					LEFT JOIN inv_transaccion ON inv_producto.codproducto=inv_transaccion.codproducto AND flgactivo='1'
				WHERE inv_producto.flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	// total de registros por auditor fecha
	public function selec_total_producto($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
				FROM inv_producto LEFT JOIN inv_transaccion ON inv_producto.codproducto=inv_transaccion.codproducto AND flgactivo='1'
				WHERE inv_producto.flag='1' $searchQuery " ;
				
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_usuarios($id_pais){
		unset($this->listas);
		$sql="SELECT codusuario as id_usuario, fullusuario as nombres 
				FROM inv_usuario
				WHERE flag='1' 
				ORDER BY 2 ";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	
	
	public function selec_one_producto($codproducto){
		
		$sql="SELECT inv_producto.*, marca, tipodsc
				FROM inv_producto LEFT JOIN inv_marca ON  inv_producto.codmarca=inv_marca.codmarca
					INNER JOIN inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo
				where codproducto=$codproducto ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_one_transacccion($codtransaccion){
		
		$sql="SELECT *,DATE_FORMAT(fecha,'%d/%m/%Y') AS fechaf, DATE_FORMAT(fecharetiro,'%d/%m/%Y') AS fecharetirof 
				FROM inv_transaccion where codtransaccion=$codtransaccion ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_transacc_producto($codproducto){
		$sql="SELECT *,DATE_FORMAT(fecha,'%d/%m/%Y') AS fechaf	
				FROM inv_transaccion where codproducto=$codproducto and flgactivo='1'";
		$consulta=$this->db->consultarOne($sql);
		
        return $consulta;	
		
	}
	 public function insert_producto($producto,$descripcion,$codmarca,$codtipo,$modelo,$serie,$ram,$procesador,$host,$hd1,$id_pais,$usuario,$ip){

        $sql="insert into inv_producto(ram,procesador,host,hd1,producto,descripcion,codmarca,codtipo,modelo,serie,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$ram','$procesador','$host','$hd1','$producto','$descripcion',$codmarca,$codtipo,'$modelo','$serie','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_producto($codproducto,$producto,$descripcion,$codmarca,$codtipo,$modelo,$serie,$ram,$procesador,$host,$hd1,$usuario,$ip){
	   
        $sql="update inv_producto 
				set 
					producto='$producto',
					descripcion='$descripcion',
					codmarca='$codmarca',
					codtipo='$codtipo',
					modelo='$modelo',
					serie='$serie',
					ram='$ram',
					procesador='$procesador',
					host='$host',
					hd1='$hd1',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codproducto=$codproducto";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_producto($codproducto){
	   
        $sql="update inv_producto set flag='0' where codproducto=$codproducto";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	


	public function insert_transaccion($codproducto,$coddestino,$descripcion,$fecha,$so,$office,$dominio,$antivirus,$onedrive,$monitor,$mouse,$audifonos,$id_pais,$usuario,$ip){

        $sql="insert into inv_transaccion(so,office,dominio,antivirus,onedrive,monitor,mouse,audifonos,codproducto,coddestino,descripcion,fecha,flgactivo,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$so','$office','$dominio','$antivirus','$onedrive','$monitor','$mouse','$audifonos',$codproducto,$coddestino,'$descripcion','$fecha','1','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	
	
	public function update_transaccion($codtransaccion,$codproducto,$coddestino,$descripcion,$fecha,$so,$office,$dominio,$antivirus,$onedrive,$monitor,$mouse,$audifonos,$usuario,$ip){
	   
        $sql="update inv_transaccion 
				set 
					descripcion='$descripcion',
					coddestino='$coddestino',
					fecha='$fecha',
					so='$so',
					office='$office',
					dominio='$dominio',
					antivirus='$antivirus',
					onedrive='$onedrive',
					monitor='$monitor',
					mouse='$mouse',
					audifonos='$audifonos',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codtransaccion=$codtransaccion and codproducto=$codproducto";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function update_transaccion_usuario($codtransaccion){
		 $sql="UPDATE inv_transaccion INNER JOIN inv_usuario ON inv_transaccion.coddestino=inv_usuario.codusuario
			SET inv_transaccion.usuariodestino=inv_usuario.fullusuario
			WHERE codtransaccion=$codtransaccion ";
		$consulta=$this->db->execute($sql);
        return $consulta;
	}

	public function update_transaccion_des($codtransaccion,$descripcion,$fecharetiro,$usuario,$ip){
		 $sql="UPDATE inv_transaccion
				set fecharetiro='$fecharetiro',
					descripcion='$descripcion',
					flgactivo='0',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
				WHERE codtransaccion=$codtransaccion ";

		$consulta=$this->db->execute($sql);
        return $consulta;
	}
}
?>