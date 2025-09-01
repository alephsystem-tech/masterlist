<?php
class lab_resultado_model{
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

	public function select_laboratorios(){
		unset($this->listas);
		$sql="select codlaboratorio,nombre from lab_nombre where flag='1' order by nombre";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_one_laboratorio($codlaboratorio){
		$sql="select codlaboratorio,nombre from lab_nombre where codlaboratorio=$codlaboratorio";
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function select_cultivos(){
		unset($this->listas);
		$sql="select codcultivo,nombre from lab_cultivo where flag='1' order by nombre";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_one_cultivo($codcultivo){
		$sql="select codcultivo,nombre from lab_cultivo where codcultivo=$codcultivo";
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function select_productos(){
		unset($this->listas);
		$sql="select codproducto,nombre from lab_producto where flag='1' order by nombre";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_one_producto($codproducto){
		$sql="select codproducto,nombre from lab_producto where codproducto=$codproducto";
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	
	public function select_resultado($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT fecha, codresultado,project_id,proyecto,pais,responsable,laboratorio,
					cultivo,producto,analisis,nrolaboratorio,
					 nromuestra_cliente,nromuestracu,date_format(fecha,'%d/%m/%y') as fecha_f,
					 date_format(fechafacturacliente,'%d/%m/%y') as fechafacturacliente_f,
					 date_format(fechaenvio,'%d/%m/%y') as fechaenvio_f,
					 resultado,nrofactura,preciodol, facturacu,
					 concat_ws(' ',project_id,proyecto) as proyectofull,
					 montocliente
              FROM lab_resultado
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

	public function select_resultadoXls($columnName,$columnSortOrder,$searchQuery){
		unset($this->listas);
		$sql="SELECT fecha, codresultado,project_id,proyecto,pais,responsable,laboratorio,cultivo,producto,analisis,nrolaboratorio,
					 nromuestra_cliente,nromuestracu,date_format(fecha,'%d/%m/%y') as fecha_f,
					 date_format(fechafacturacliente,'%d/%m/%y') as fechafacturacliente_f,
					 date_format(fechaenvio,'%d/%m/%y') as fechaenvio_f,
					 resultado,nrofactura,preciodol, facturacu,
					 concat_ws(' ',project_id,proyecto) as proyectofull,
					 montocliente
              FROM lab_resultado
	      WHERE flag = '1'  $searchQuery ";
		$sql.="group by codresultado order by ".$columnName." ".$columnSortOrder;

		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	// total de registros por auditor fecha
	public function selec_total_resultado($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total FROM   lab_resultado    WHERE flag='1' $searchQuery " ;
		
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_resultado($id_actividad){
		
		$sql="SELECT * from prg_actividad where id_actividad=$id_actividad ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function select_data_estadistica($id_pais,$anio){
		unset($this->listas);
		$sql="SELECT ROUND(SUM( IFNULL(montocliente,0) ),1) AS costo, MONTH(fechaenvio) AS mes,
				COUNT(codresultado) AS numero
				FROM lab_resultado
				WHERE flag='1' AND YEAR(fechaenvio)=$anio and id_pais='$id_pais'";
		 $sql.="  GROUP BY 2
            ORDER BY 2";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;		
		
	}
	
	
	 public function insert_resultado($data_sql,$codunico,$id_pais){

		$sql="truncate  lab_resultado_tmp";
		$consulta=$this->db->execute($sql);
		
        $sql="insert into lab_resultado_tmp(
					project_id ,proyecto,modulo,pais,unidad,responsable,laboratorio,cultivo,producto,
					categoria, motivo,tipoinspeccion,analisis,nrolaboratorio,nromuestra_cliente,nromuestracu,
					fecha,fechaenvio,fecharesultado,
					diferencia,nroreporteanalisis,resultado,molencontradas,nivel,tiporesiduo,fechaentregacli,flgcolgadocusi,
					nrofactura,preciodol,montocliente,botomuestra,facturacu,codunico
				) values $data_sql";
		
		$consulta=$this->db->execute($sql);
		/*
		$sql="UPDATE lab_resultado_tmp
						SET
								fecha= CASE WHEN SUBSTRING(fecha,7,2)!='20' THEN NULL ELSE fecha END,
								fechaenvio= CASE WHEN SUBSTRING(fechaenvio,7,2)!='20' THEN NULL ELSE fechaenvio END,
								fecharesultado= CASE WHEN SUBSTRING(fecharesultado,7,2)!='20' THEN NULL ELSE fecharesultado END,
								fechaentregacli= CASE WHEN SUBSTRING(fechaentregacli,7,2)!='20' THEN NULL ELSE fechaentregacli END ,
								fechafacturacliente= CASE WHEN SUBSTRING(fechafacturacliente,7,2)!='20' THEN NULL ELSE fechafacturacliente END 
						where codunico='$codunico'";

		$consulta=$this->db->execute($sql);
        
		$sql="UPDATE lab_resultado_tmp
				SET 
					fecha=CONCAT_WS('/',SUBSTRING(fecha,7,4), SUBSTRING(fecha,4,2) ,SUBSTRING(fecha,1,2)) ,
					fechaenvio=CONCAT_WS('/',SUBSTRING(fechaenvio,7,4), SUBSTRING(fechaenvio,4,2) ,SUBSTRING(fechaenvio,1,2)) ,
					fecharesultado=CONCAT_WS('/',SUBSTRING(fecharesultado,7,4), SUBSTRING(fecharesultado,4,2) ,SUBSTRING(fecharesultado,1,2)) ,
					fechaentregacli=CONCAT_WS('/',SUBSTRING(fechaentregacli,7,4), SUBSTRING(fechaentregacli,4,2) ,SUBSTRING(fechaentregacli,1,2)),
					fechafacturacliente=CONCAT_WS('/',SUBSTRING(fechafacturacliente,7,4), SUBSTRING(fechafacturacliente,4,2) ,SUBSTRING(fechafacturacliente,1,2))
				where codunico='$codunico'";
		$consulta=$this->db->execute($sql);
		*/
		
		/*$sql=" update lab_resultado_tmp set fechaentregacli=null where fechaentregacli=''";
		$consulta=$this->db->execute($sql);*/
		
		/*$sql=" delete from lab_resultado_tmp where nrolaboratorio is null";
		$consulta=$this->db->execute($sql);*/
		
		$sql="select count(*) as total
				 from lab_resultado_tmp 
				 where nrolaboratorio not in (select nrolaboratorio from  lab_resultado  where flag='1' and id_pais='$id_pais' )
				and codunico='$codunico'";
		$consulta=$this->db->consultarOne($sql);
		
		$total=$consulta['total'];
		
	
		return $total;
    }	

	public function monto_insert_resultado($codunico,$id_pais,$tcEU){
		$sql="select ROUND(SUM( IFNULL(montocliente,0) ),1) as monto from lab_resultado_tmp where  nrolaboratorio not in (select nrolaboratorio from lab_resultado where flag='1' and id_pais='$id_pais')";
		$consulta=$this->db->consultarOne($sql);
		$monto=$consulta['monto'];
		return $monto;
    }

	public function insert_resultado_2($codunico,$id_pais){
		
		$sql="insert into lab_nombre(nombre,flag)
				select distinct laboratorio,'1' from lab_resultado_tmp where laboratorio not in (select nombre from lab_nombre) and codunico='$codunico'";
		$consulta=$this->db->execute($sql);
		
		$sql="insert into lab_cultivo(nombre,flag)
				select distinct cultivo,'1' from lab_resultado_tmp where cultivo not in (select nombre from lab_cultivo) and codunico='$codunico'";
		$consulta=$this->db->execute($sql);
		
		$sql="insert into lab_producto(nombre,flag)
				select distinct producto,'1' from lab_resultado_tmp where producto not in (select nombre from lab_producto) and codunico='$codunico'";
		$consulta=$this->db->execute($sql);
		
		/*$sql="UPDATE lab_resultado_tmp l
				SET 
						codboto= (SELECT codboto FROM lab_tipoboto WHERE boto=botomuestra) ,
						codpais=(SELECT id_pais FROM t_mae_pais WHERE nombre=pais) ,
						codlaboratorio=(SELECT codlaboratorio FROM lab_nombre WHERE nombre=laboratorio) ,
						codanalisis=(SELECT codanalisis FROM lab_tipoanalisis WHERE analisis=l.analisis) ,
						codcultivo=(SELECT codcultivo FROM lab_cultivo  WHERE nombre=cultivo ) ,
						codproducto=(SELECT codproducto FROM lab_producto  WHERE nombre=producto ) ,
						codresponsable=(SELECT id_auditor FROM prg_auditor WHERE iniciales=responsable LIMIT 0,1) ,
						codtiporesultado=(SELECT codtiporesultado FROM lab_tiporesultado  WHERE nombre=resultado LIMIT 0,1) 
				where codunico='$codunico'	";
         $consulta=$this->db->execute($sql);*/

		//$sql="UPDATE lab_resultado_tmp SET fechaenvio=DATE_ADD(fechaenvio, INTERVAL -1 DAY) where codunico='$codunico'";
		//$consulta=$this->db->execute($sql);
		

		/*$sql="update lab_resultado w inner join lab_resultado_tmp t on  w.nrolaboratorio=t.nrolaboratorio
				set w.nrofactura=t.nrofactura, w.facturacu=t.facturacu,w.preciodol=t.preciodol,
					w.montocliente=t.montocliente,w.fechaenvio=t.fechaenvio
				where w.nrolaboratorio  =t.nrolaboratorio
					and codunico='$codunico'";		 
		$consulta=$this->db->execute($sql);*/
		
		
		$sql="select count(*) as total
				 from lab_resultado_tmp 
				 where nrolaboratorio not in (select nrolaboratorio from  lab_resultado  where flag='1' and id_pais='$id_pais'  )
				and codunico='$codunico'";
		$consulta=$this->db->consultarOne($sql);
		
		$total=$consulta['total'];
		
		$sql="insert into lab_resultado( id_pais, project_id ,proyecto,pais,unidad,responsable,laboratorio,cultivo,producto,
							analisis,nrolaboratorio,nromuestra_cliente,nromuestracu,fecha,fechaenvio,fecharesultado,
							diferencia,nroreporteanalisis,resultado,molencontradas,nivel,fechaentregacli,flgcolgadocusi,
							nrofactura,preciodol,botomuestra,montocliente, codboto,codpais,codlaboratorio,
							codanalisis,codcultivo,codproducto,codresponsable,codtiporesultado,
							flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica,
							modulo,categoria,motivo,tipoinspeccion,tiporesiduo,facturacu,fechafacturacliente) 
				 select '$id_pais',project_id ,proyecto,pais,unidad,responsable,laboratorio,cultivo,producto,
						analisis,nrolaboratorio,nromuestra_cliente,nromuestracu,fecha,fechaenvio,fecharesultado,
						diferencia,nroreporteanalisis,resultado,molencontradas,nivel,fechaentregacli,flgcolgadocusi,
						nrofactura,preciodol,botomuestra,montocliente, codboto,codpais,codlaboratorio,
						codanalisis,codcultivo,codproducto,codresponsable,codtiporesultado,
						'1','admin',now(),'local','admin',now(),'local',
						modulo,categoria,motivo,tipoinspeccion,tiporesiduo,facturacu,fechafacturacliente
				 from lab_resultado_tmp 
				 where nrolaboratorio not in (select nrolaboratorio from  lab_resultado   where flag='1' and id_pais='$id_pais')
				and codunico='$codunico'";
		$consulta=$this->db->execute($sql);
		
		return $total;
	}
	
	public function select_data_temporalLab($codunico){
		unset($this->listas);
				
		$sql="SELECT lab_resultado_tmp.*, ifnull(lab_resultado.codresultado,'') as litc	
			FROM lab_resultado_tmp left join lab_resultado on lab_resultado_tmp.nrolaboratorio=lab_resultado.nrolaboratorio 
			where codunico='$codunico'";

		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function delete_labResultado($codresultado){
        //$sql="update lab_resultado set flag='0' where codresultado=$codresultado";
		$sql="delete from lab_resultado where codresultado=$codresultado";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	
}
?>