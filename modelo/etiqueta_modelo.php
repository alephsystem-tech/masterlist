<?php
class etiqueta_model{
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

	
	
	public function select_etiqueta($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT *,date_format(fecrecepcion,'%d/%m/%y') as fecrecepcion_f,
					 date_format(fecaprobacion,'%d/%m/%y') as fecaprobacion_f,
					 concat_ws(' ',project_id,proyecto) as proyectofull
              FROM etiqueta
	      WHERE flag = '1'  $searchQuery ";
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
	public function selec_total_etiqueta($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total FROM etiqueta WHERE flag='1' $searchQuery " ;
		
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_etiqueta($codetiqueta){
		
		$sql="SELECT * from etiqueta where codetiqueta=$codetiqueta ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function select_data_estadistica($id_pais,$anio){
		unset($this->listas);
		$sql="SELECT ROUND(SUM( IFNULL(preciodol,0) ),1) AS costo, 
				MONTH(fecaprobacion) AS mes,
				year(fecaprobacion) AS anio,
				COUNT(codetiqueta) AS numero
				FROM etiqueta
				WHERE flag='1' AND YEAR(fecaprobacion)>= ($anio-1) and id_pais='$id_pais'
				GROUP BY 2,3
				ORDER BY 2";
				
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;		
		
	}
	
	
	 public function insert_resultado($data_sql,$codunico,$id_pais){

		$sql="truncate  etiqueta_tmp";
		$consulta=$this->db->execute($sql);

        $sql="insert into etiqueta_tmp(
					codigo,fecrecepcion,fecaprobacion,duracion,project_id,proyecto,
						pais,asistente,private,cliente,accion,producto,solicitante,preciodol,
						comentarios,id_pais,usuario_ingreso,ip_ingreso,codunico
				) values $data_sql";
	
		$consulta=$this->db->execute($sql);
		
		/*
		$sql="UPDATE etiqueta_tmp
						SET
								fecrecepcion= CASE WHEN SUBSTRING(fecrecepcion,7,2)!='20' THEN NULL ELSE fecrecepcion END,
								fecaprobacion= CASE WHEN SUBSTRING(fecaprobacion,7,2)!='20' THEN NULL ELSE fecaprobacion END
						where codunico='$codunico'";

		$consulta=$this->db->execute($sql);
        
		$sql="UPDATE etiqueta_tmp
				SET 
					fecrecepcion=CONCAT_WS('/',SUBSTRING(fecrecepcion,7,4), SUBSTRING(fecrecepcion,4,2) ,SUBSTRING(fecrecepcion,1,2)) ,
					fecaprobacion=CONCAT_WS('/',SUBSTRING(fecaprobacion,7,4), SUBSTRING(fecaprobacion,4,2) ,SUBSTRING(fecaprobacion,1,2)) 
				where codunico='$codunico'";
		$consulta=$this->db->execute($sql);
		*/
		
		$sql=" delete from etiqueta_tmp where codigo is null";
		$consulta=$this->db->execute($sql);
		
		$sql="select count(*) as total
				 from etiqueta_tmp 
				 where codigo not in (select codigo from  etiqueta  where flag='1' and id_pais='$id_pais')
				and codunico='$codunico'";
		$consulta=$this->db->consultarOne($sql);
		
		$total=$consulta['total'];

		return $total;
    }	

	public function insert_resultado_2($codunico,$id_pais){
		$sql="select count(*) as total
				 from etiqueta_tmp 
				 where codigo not in (select codigo from  etiqueta  where flag='1' and id_pais='$id_pais' )
				and codunico='$codunico'";
		$consulta=$this->db->consultarOne($sql);
		
		$total=$consulta['total'];
		
		$sql="insert into etiqueta( codigo,fecrecepcion,fecaprobacion,duracion,project_id,proyecto,id_pais,
						pais,asistente,private,cliente,accion,producto,solicitante,preciodol,
						comentarios,usuario_ingreso,fecha_ingreso,ip_ingreso) 
				 select codigo,fecrecepcion,fecaprobacion,duracion,project_id,proyecto,id_pais,
					pais,asistente,private,cliente,accion,producto,solicitante,preciodol,
					comentarios,usuario_ingreso,fecha_ingreso,ip_ingreso
				 from etiqueta_tmp 
				 where etiqueta_tmp.codigo not in (select codigo from  etiqueta  where flag='1' and id_pais='$id_pais')
				and codunico='$codunico'";
				
		$consulta=$this->db->execute($sql);
		
		return $total;
	}

	public function monto_insert_resultado($codunico,$id_pais){
		$sql="select ROUND(SUM(preciodol),1) as monto from etiqueta_tmp where codigo not in (select codigo from etiqueta where flag='1' and id_pais='$id_pais') and codunico='$codunico'";
		$consulta=$this->db->consultarOne($sql);
		$monto=$consulta['monto'];
		return $monto;
    }	
	
	public function select_data_temporalEtiqueta($codunico){
		unset($this->listas);
				
		$sql="SELECT etiqueta_tmp.*, ifnull(etiqueta.codigo,'') as litc	
			FROM etiqueta_tmp left join etiqueta on etiqueta_tmp.codigo=etiqueta.codigo 
			where codunico='$codunico'";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function delete_etiqueta($codetiqueta){
        //$sql="update etiqueta set flag='0' where codetiqueta=$codetiqueta";
		$sql="delete from etiqueta where codetiqueta=$codetiqueta";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	
}
?>