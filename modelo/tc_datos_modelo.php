<?php
class tc_datos_model{
    private $db;
	private $listas;
    public function __construct(){
        $this->db=new DBManejador();
		$this->listas=array();
    }
    /* MODELO para seleccionar  paises
        junio 2021
		Autor: Enrique Bazalar alephsystem@gmail.com project_id
    */

		
	public function select_resultado($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT
                concat_ws(' ',cu,clientefinal) as clientefinalfull,project_id,clientefinal,
				asistente,fecha,subprograma ,tipo ,trc ,itc ,traces ,opcion ,cu,proyecto,producto,pais_origen ,lote,
                pais_destino,volumen,grupo ,individual ,cui ,cliente ,modo_envio ,costo_eu ,costo_usd ,cos_courier_usd,
				ifnull(costo_usd,2) + ifnull(cos_courier_usd,2) as costo,
                nrotrk,codtc ,  date_format(fecha_emision,'%d.%m.%y') as  fecha_emision ,
				ifnull(date_format(fechafactura,'%d.%m.%y'),'') as fechafactura,
                ifnull(consignie,'') as consignie,
				concat_ws(' ',project_id,proyecto) as proyectofull
			FROM tc_datos 
            WHERE tc_datos.flag='1'   $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);	
		if(!empty($consulta)){		
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}	
        return $this->listas;	
		
	}
		
	public function select_resultadoXls($columnName,$columnSortOrder,$searchQuery,$tcEU){
		unset($this->listas);
		$sql="SELECT
                clientefinal,asistente,subprograma ,tipo ,trc ,itc ,traces ,opcion ,cu,proyecto,producto,pais_origen ,lote,
                pais_destino,volumen,grupo ,individual ,cui ,cliente ,modo_envio , project_id,
				ifnull(costo_eu,0) costo_eu , ifnull(costo_usd,0) costo_usd, 
				cos_courier_usd,
				ROUND(IFNULL(costo_usd,0) + IFNULL(cos_courier_usd,0) + (IFNULL(costo_eu,0)*$tcEU),2) AS costo,
                nrotrk,codtc ,  date_format(fecha_emision,'%d.%m.%y') as  fecha_emision ,
				ifnull(date_format(fechafactura,'%d.%m.%y'),'') as fechafactura,
                ifnull(consignie,'') as consignie,
				concat_ws(' ',cu,proyecto) as proyectofull
			FROM tc_datos 
            WHERE tc_datos.flag='1' $searchQuery ";
		$sql.=" group by codtc order by ".$columnName." ".$columnSortOrder ."";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	// total de registros por auditor fecha
	public function selec_total_resultado($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total FROM   tc_datos  WHERE flag='1' $searchQuery " ;
		
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	

	public function select_data_estadistica($id_pais,$anio,$tcEU){
		unset($this->listas);
		$sql="SELECT ROUND(SUM( IFNULL(costo_usd,0) + IFNULL(cos_courier_usd,0) + (IFNULL(costo_eu,0)*$tcEU)),1) AS costo, 
				MONTH(fecha_emision) AS mes,
				year(fecha_emision) AS anio,
				COUNT(codtc) AS numero
				FROM tc_datos 
				WHERE flag='1' and id_pais = '$id_pais'";
		 $sql.="  GROUP BY 2,3  ORDER BY 3,2";
		 
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;		
		
	}
	
	public function select_data_temporalTC($id_pais){
		unset($this->listas);
		$sql="SELECT tc_datos_tmp.*,
				IFNULL(tc_datos.itc,'') AS litc,
				IFNULL(prg_proyecto.project_id,'') AS cutmp
			FROM tc_datos_tmp LEFT JOIN tc_datos ON tc_datos_tmp.itc=tc_datos.itc 
				LEFT JOIN prg_proyecto ON TRIM(tc_datos_tmp.project_id)=prg_proyecto.project_id AND ( tc_datos_tmp.pais_origen=prg_proyecto.country OR prg_proyecto.id_pais='$id_pais')
			where prg_proyecto.flag='1' and prg_proyecto.flgactivo='1' ";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;		
		
	}
	
	public function insert_resultado($data_sql,$codunico,$id_pais){
        $sql="truncate  tc_datos_tmp";
		$consulta=$this->db->execute($sql);
		
		$sql="insert into tc_datos_tmp(  asistente,fecha,subprograma,
						tipo,trc,itc,traces,opcion,project_id,proyecto,producto,
						pais_origen,fecha_emision,lote,pais_destino,volumen,grupo,
						individual,cui,cliente,modo_envio,costo_eu,costo_usd,
						cos_courier_usd,nrotrk,contacto,observacion,consignie,cu,clientefinal ,fechafactura) values $data_sql ";
		
		$consulta=$this->db->execute($sql);
		
		$sql="select count(*) as total from tc_datos_tmp 
				where  itc not in (select itc from tc_datos where flag='1' and id_pais='$id_pais')
					and project_id in (select project_id from prg_proyecto where flag='1' and flgactivo='1' and (country=tc_datos_tmp.pais_origen OR id_pais='$id_pais')) ";
		$consulta=$this->db->consultarOne($sql);
		$total=$consulta['total'];
		
		return $total;
		
		
    }	

	public function monto_insert_resultado($codunico,$id_pais,$tcEU){
		$sql="select ROUND(SUM( IFNULL(costo_usd,0) + IFNULL(cos_courier_usd,0) + (IFNULL(costo_eu,0)*$tcEU)),1) as monto 
			from tc_datos_tmp 
			where  itc not in (select itc from tc_datos where flag='1' and id_pais='$id_pais')
			and project_id in (select project_id from prg_proyecto where  prg_proyecto.flag='1' and prg_proyecto.flgactivo='1' and (country=tc_datos_tmp.pais_origen OR id_pais='$id_pais'))  ";
		$consulta=$this->db->consultarOne($sql);
		$monto=$consulta['monto'];
		return $monto;
    }	
	
	public function insert_resultado_2($id_pais){
       
		$sql="update tc_datos w inner join tc_datos_tmp t on  w.itc=t.itc
						set w.fechafactura=t.fechafactura
						where ifnull(t.fechafactura,'')!='' and ifnull(w.fechafactura,'')=''";
		$consulta=$this->db->execute($sql);
		
	
		$sql="select count(*) as total from tc_datos_tmp 
				where  itc not in (select itc from tc_datos where flag='1' and id_pais='$id_pais')
				and project_id in (select project_id from prg_proyecto where prg_proyecto.flag='1' and prg_proyecto.flgactivo='1' and (country=tc_datos_tmp.pais_origen OR id_pais='$id_pais'))  ";
		$consulta=$this->db->consultarOne($sql);
		$total=$consulta['total'];
		
		$sql="insert into tc_datos(  montocliente,monedacliente, asistente,fecha,subprograma,
					tipo,trc,itc,traces,opcion,cu,proyecto,producto,
					pais_origen,fecha_emision,lote,pais_destino,volumen,grupo,
					individual,cui,cliente,modo_envio,costo_eu,costo_usd,
					cos_courier_usd,nrotrk,clientefinal,project_id ,
					flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica,consignie,
					contacto,observacion,id_pais,fechafactura) 
					
					select (ifnull(costo_usd,0) + ifnull(cos_courier_usd,0) + (ifnull(costo_eu,0)*1.17)) as montocliente, 
						'US$' as monedacliente,asistente,
						fecha,
						subprograma,
						tipo,trc,itc,traces,opcion,cu,proyecto,producto,
						pais_origen,
						fecha_emision,
						lote,pais_destino,volumen,grupo,
						individual,cui,cliente,modo_envio,costo_eu,costo_usd,
						cos_courier_usd,nrotrk , clientefinal,project_id,
						'1','admin',now(),'local','admin',now(),'local',consignie,contacto,observacion,
						'$id_pais',
						fechafactura
					  from tc_datos_tmp 
					  where  itc not in (select itc from tc_datos where flag='1' and id_pais='$id_pais')
						and project_id in (select project_id from prg_proyecto where prg_proyecto.flag='1' and prg_proyecto.flgactivo='1' and (country=tc_datos_tmp.pais_origen  OR id_pais='$id_pais')) ";
		$consulta=$this->db->execute($sql);
		
		return $total;
		
		
    }	

	public function delete_Tc($codtc){
        //$sql="update tc_datos set flag='0' where codtc=$codtc";
		$sql="delete from tc_datos where codtc=$codtc";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	
}
?>