<?php
class kpi_programa_model{
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

	public function selec_prgprograma($id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="select descripcion,id_programa 
				from prg_programa 
				where flag='1' and id_pais='$id_pais' and flgactivo='1'  AND descripcion!='' 
				order by descripcion";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_indicador($id_pais,$codprograma,$tipokpi){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT codindicador,indicador 
				FROM kpi_indicador WHERE flag='1' AND id_pais='$id_pais' and tipokpi='$tipokpi'
					AND codindicador NOT IN (SELECT codindicador FROM kpi_programaxindicador WHERE codprograma=$codprograma)
				ORDER BY indicador";
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;			
	}

	public function select_kpiprograma($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT kpi_programa.*,group_concat(concat_ws('=>',i.indicador,pi.peso) SEPARATOR '<br>') as indicador
				from kpi_programa left join kpi_programaxindicador pi on kpi_programa.codprograma=pi.codprograma
					left join kpi_indicador i on  pi.codindicador=i.codindicador and i.flag='1'
				where kpi_programa.flag='1'  $searchQuery 
				group by kpi_programa.codprograma ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	
	// total de registros 
	public function selec_total_kpiprograma($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            kpi_programa
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_kpiprograma($codprograma){
		
		$sql="SELECT *
				from kpi_programa where codprograma=$codprograma ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}

	
	 public function insert_kpiprograma($tipokpi,$emailsuper,$programa,$id_programa,$flgfail,$flgvalida,$id_pais,$usuario,$ip){

        $sql="insert into kpi_programa (tipokpi,emailsuper,programa,id_programa,flgfail,flgvalida,id_pais,flag, usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$tipokpi','$emailsuper','$programa','$flgfail','$flgvalida','$id_programa','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_kpiprograma($tipokpi,$emailsuper,$codprograma,$programa,$id_programa,$flgfail,$flgvalida,$id_pais,$usuario,$ip){
	   
        $sql="update kpi_programa 
				set programa='$programa',flgfail='$flgfail',
					flgvalida='$flgvalida',
					tipokpi='$tipokpi',
					id_programa='$id_programa', 
				emailsuper='$emailsuper', 
				usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codprograma=$codprograma";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_kpiprograma($codprograma){
	   
        $sql="update kpi_programa set flag='0' where codprograma=$codprograma";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	


	public function selec_one_kpiprograma_ind($codprograma,$codindicador){
		$sql="SELECT *
				from kpi_programaxindicador where codprograma=$codprograma and codindicador=$codindicador";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	
	public function selec_kpiprograma_indicad($codprograma){
		unset($this->listas);
		$sql="SELECT kpi_programaxindicador.*, indicador
			from kpi_programaxindicador inner join kpi_indicador on kpi_programaxindicador.codindicador=kpi_indicador.codindicador
			where codprograma=$codprograma
			order by indicador";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;
		
	}
	
	
	public function insert_kpiprograma_ind($codprograma,$codindicador,$peso,$id_pais,$usuario,$ip){
		 $sql="insert into kpi_programaxindicador (codprograma,codindicador,peso)
        values('$codprograma','$codindicador','$peso')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
	}
	
	public function selec_pesoindicador($id_pais,$codprograma){
		$sql="SELECT ifnull(sum(peso),0) as peso from kpi_programaxindicador where codprograma=$codprograma ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	
	public function delete_kpiprogramaxindicador($codprograma,$codindicador){
	   
        $sql="delete from  kpi_programaxindicador where codprograma=$codprograma and codindicador=$codindicador";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
}
?>