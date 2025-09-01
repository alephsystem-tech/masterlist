<?php
class prg_proyectocosto_model{
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

		
	
	 public function insert_proyectocosto($data_sql,$codunico,$id_pais){
		
		$sql="truncate  prg_proyectocosto_tmp";
		$consulta=$this->db->execute($sql);
		
		$sql="insert into prg_proyectocosto_tmp(proyecto,fecha,vencimiento,nrofactura,usuario,importe,concepto,recordatorio1,recordatorio2,id_pais) 
			values $data_sql";
		$consulta=$this->db->execute($sql);
		/*
		$sql=" UPDATE prg_proyectocosto_tmp SET 
				fecha_f=CONCAT_WS('/',
						SUBSTRING(fecha,7,4) ,
						SUBSTRING(fecha,4,2) ,
						SUBSTRING(fecha,1,2) ) ,
				vencimiento_f=CONCAT_WS('/',
						SUBSTRING(vencimiento,7,4) ,
						SUBSTRING(vencimiento,4,2) ,
						SUBSTRING(vencimiento,1,2) ),
				recordatorio1_f=CONCAT_WS('/',
						SUBSTRING(recordatorio1,7,4) ,
						SUBSTRING(recordatorio1,4,2) ,
						SUBSTRING(recordatorio1,1,2) ),
				recordatorio2_f=CONCAT_WS('/',
						SUBSTRING(recordatorio2,7,4) ,
						SUBSTRING(recordatorio2,4,2) ,
						SUBSTRING(recordatorio2,1,2) )
				";
		*/		
		$sql=" UPDATE prg_proyectocosto_tmp SET 
				fecha_f=fecha ,
				vencimiento_f=vencimiento,
				recordatorio1_f=recordatorio1,
				recordatorio2_f=recordatorio2 ";
				
		$consulta=$this->db->execute($sql);
		
		
		$sql=" UPDATE prg_proyectocosto_tmp p INNER JOIN prg_usuarios u ON p.usuario=u.usuario
				SET p.id_auditor=u.id_auditor, p.f_auditor=u.nombres";
		$consulta=$this->db->execute($sql);
	
		
		unset($this->listas);
		$sql="select * from prg_proyectocosto_tmp";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;
	
    }	

	public function regula_proyectocosto($codpais){
		
		$sql="truncate prg_proyectocosto ";
		$consulta=$this->db->execute($sql);
		
		$sql="INSERT INTO prg_proyectocosto (proyecto,fecha_f,nrofactura,importe,concepto,vencimiento_f,usuario,
				id_auditor,f_auditor,recordatorio1_f,recordatorio2_f,id_pais)
				SELECT proyecto,fecha_f,nrofactura,importe,concepto,vencimiento_f,usuario,id_auditor,f_auditor ,recordatorio1_f,recordatorio2_f, id_pais
				FROM prg_proyectocosto_tmp
				where flag='1'";
		$consulta=$this->db->execute($sql);

	}

	public function select_reporte_proyectocosto($project_id){
		unset($this->listas);
		$this->listas=[];
			$sql="select * from prg_proyectocosto where proyecto='$project_id'";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}


	public function select_reporte_deuda($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT prg_proyectocosto.*, date_format(fecha_f,'%d.%m.%y') as fecha,
				date_format(vencimiento_f,'%d.%m.%y') as vencimiento,
				date_format(recordatorio1_f,'%d.%m.%y') as recordatorio1,
				date_format(recordatorio2_f,'%d.%m.%y') as recordatorio2,
				prg_proyecto.proyect as nombre_proyecto
			FROM prg_proyectocosto
			LEFT JOIN prg_proyecto on prg_proyecto.project_id=prg_proyectocosto.proyecto
			
			WHERE  1=1 $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_total_reporte_deuda($searchQuery){
		
		$sql="SELECT COUNT(*) AS total
			FROM prg_proyectocosto
			WHERE  1=1  $searchQuery " ;
	
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
		
	}
	
	public function selec_DeudabyProy($project_id,$id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="select * from prg_proyectocosto where id_pais='$id_pais' and proyecto='$project_id'";
				
		$consulta=$this->db->consultar($sql);	
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_one_DeudabyProy($coddetalle,$id_pais){
		$sql="select * ,
				date_format(fecha_f,'%d/%m/%Y') as fecha,
				date_format(vencimiento_f,'%d/%m/%Y') as vencimiento,
				date_format(recordatorio1_f,'%d/%m/%Y') as recordatorio1,
				date_format(recordatorio2_f,'%d/%m/%Y') as recordatorio2
			from prg_proyectocosto where coddetalle=$coddetalle";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
	}
		
	public function update_detalleProyDeuda($coddetalle,$concepto,$importe,$fecha,$vencimiento,$recordatorio1,$recordatorio2,$nrofactura,$observacion,$usuario,$ip,$id_pais){
		$sql="update prg_proyectocosto
				set
					concepto='$concepto',
					observacion='$observacion',
					importe='$importe',
					fecha_f='$fecha',
					vencimiento_f='$vencimiento',
					recordatorio1_f='$recordatorio1',
					recordatorio2_f='$recordatorio2',
					nrofactura='$nrofactura'
				where coddetalle=$coddetalle";
		$consulta=$this->db->execute($sql);
        return $consulta;	
	}
	
}
?>