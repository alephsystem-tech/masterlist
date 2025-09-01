<?php
class kpi_reporte_model{
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

	public function select_kpiaccion($tipokpi='',$id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT * from kpi_accion	where  flag='1' and tipokpi='$tipokpi' and id_pais='$id_pais'";
	
		$consulta=$this->db->consultar($sql);	
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	
	public function select_kpireporte($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_pais,$tipokpi){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT substring(fullauditor,1,15) as fullauditor,
				ifnull(fullauditor,'') as nombreauditor,
				ifnull(certificadorname,'') certificadorname,
				ifnull(usercertificador,'') usercertificador,
				ifnull(auditor,'') auditor,
				oficina, COUNT(distinct concat(kpi_importar.id,kpi_importar.item)) AS total, 
					round(AVG(stiempo),2) as stiempo,
					round(AVG(scalidad),2) as scalidad, 
					round(AVG(sproc),2) as sproc, 
					round(AVG(svolumen),2) as svolumen, 
					round(AVG(sgestion),2) as sgestion, 
					round(avg((sfinal)),2) as promedio
				FROM kpi_importar INNER JOIN kpi_importar_cab ON kpi_importar.id =kpi_importar_cab.id 
					AND	kpi_importar.codprograma =kpi_importar_cab.codprograma
				WHERE flgcerrado='1' AND kpi_importar_cab.flag='1'    $searchQuery  ";
		
		if(!empty($tipokpi)) 
			$sql.=" and kpi_importar.tipokpi='$tipokpi' ";

		if($tipokpi=='cer')
			$sql.="	GROUP BY certificadorname,oficina ";
		else
			$sql.="	GROUP BY auditor,oficina ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;

		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function select_kpireporte_det($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$id_pais,$tipokpi){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT project_id, project,insp_id,tipo,fechadraft,
					pais,certificador,oficina,oficinacontrato,
					kpi_programa.programa,subprograma,subprograma2,subprograma3,
					subprograma4,modulo,
					DATE_FORMAT(fechaevaluacion,'%d/%m/%Y') AS fechaevaluacion,	
					DATE_FORMAT(fecha,'%d/%m/%Y') AS fecha,
					DATE_FORMAT(kpi_importar_cab.fecha_ingreso,'%d/%m/%Y') AS fechaimporta,	
					IFNULL(fullevaluador,'') fullevaluador,
					IFNULL(usercertificador,'') usercertificador,
					IFNULL(fullauditor,'') AS nombreauditor,
					stiempo,
					scalidad, 
					sproc, 
					svolumen, 
					sgestion, 
					sfinal AS promedio
				FROM kpi_importar INNER JOIN kpi_importar_cab ON kpi_importar.id =kpi_importar_cab.id 
					AND	kpi_importar.codprograma =kpi_importar_cab.codprograma
					 inner join kpi_programa on kpi_importar.codprograma=kpi_programa.codprograma
				WHERE flgcerrado='1' AND kpi_importar_cab.flag='1'    $searchQuery  ";
		
		if(!empty($tipokpi)) 
			$sql.=" and kpi_importar.tipokpi='$tipokpi' ";

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
	public function selec_total_kpireporte($id_pais,$tipokpi,$searchQuery=null){
		$sql=" SELECT COUNT(distinct auditor)+1 AS total 
			FROM kpi_importar INNER JOIN kpi_importar_cab ON kpi_importar.id =kpi_importar_cab.id 
				AND	kpi_importar.codprograma =kpi_importar_cab.codprograma
			WHERE flgcerrado='1' AND kpi_importar_cab.flag='1'   $searchQuery " ;
		//if($id_pais!='esp') 
		//	$sql.=" and oficina in (select upper(nombre) from prg_paises where flag='1' and id_pais='$id_pais')";				
		
		if(!empty($tipokpi)) 
			$sql.=" and kpi_importar.tipokpi='$tipokpi' ";

			$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_total_kpireporte_cert($id_pais,$tipokpi,$searchQuery=null){
		$sql=" SELECT COUNT(distinct certificadorname) AS total 
			FROM kpi_importar INNER JOIN kpi_importar_cab ON kpi_importar.id =kpi_importar_cab.id 
				AND	kpi_importar.codprograma =kpi_importar_cab.codprograma
			WHERE flgcerrado='1' AND kpi_importar_cab.flag='1'   $searchQuery " ;
		//if($id_pais!='esp') 
		//	$sql.=" and oficina in (select upper(nombre) from prg_paises where flag='1' and id_pais='$id_pais')";				
		
		if(!empty($tipokpi)) 
			$sql.=" and kpi_importar.tipokpi='$tipokpi' ";

			$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_kpireporte($codauditor){
		
		$sql="SELECT *
				from kpi_auditor where codauditor=$codauditor ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_pais_reporte($id_pais,$tipokpi=null){
		unset($this->listas);
		$this->listas=[];
		$sql="select distinct pais 
				from kpi_importar 
				where ifnull(pais,'')!='' ";
		if(!empty($tipokpi))
				$sql.=" and tipokpi='$tipokpi' ";
		if($id_pais!='esp') $sql.=" and pais in (select upper(nombre) from prg_paises where flag='1' and id_pais='$id_pais')";
		$sql.="	order by 1";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_certificador_reporte($id_pais,$tipokpi=null){
		unset($this->listas);
		$this->listas=[];
		$sql="select distinct certificador as oficina
				from kpi_importar 
				where ifnull(certificador,'')!='' ";
		if(!empty($tipokpi))
				$sql.=" and tipokpi='$tipokpi' ";		
		if($id_pais!='esp') $sql.=" and certificador in (select upper(nombre) from prg_paises where flag='1' and id_pais='$id_pais')";
		$sql.="	order by 1";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_oficinacontrato_reporte($id_pais,$tipokpi=null){
		unset($this->listas);
		$this->listas=[];
		$sql="select distinct oficinacontrato as oficina
				from kpi_importar 
				where ifnull(oficinacontrato,'')!='' ";
		if(!empty($tipokpi))
				$sql.=" and tipokpi='$tipokpi' ";		
		if($id_pais!='esp') $sql.=" and oficinacontrato in (select upper(nombre) from prg_paises where flag='1' and id_pais='$id_pais')";
		$sql.="	order by 1";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_oficina_reporte($id_pais,$tipokpi=null){
		unset($this->listas);
		$this->listas=[];
		$sql="select distinct oficina
			  from kpi_importar 
			  where ifnull(oficina,'')!='' ";
		if(!empty($tipokpi))
				$sql.=" and tipokpi='$tipokpi' ";			  
		if($id_pais!='esp') $sql.=" and oficina in (select upper(nombre) from prg_paises where flag='1' and id_pais='$id_pais')";
		$sql.="	  order by 1";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function selec_programa_reporte($id_pais,$tipokpi){
		unset($this->listas);
		$this->listas=[];
		$sql="select codprograma, programa
			from kpi_programa 
			where flag='1' and tipokpi='$tipokpi'";
			
		$sql.="		order by 2 ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}
	
	public function select_kpigraf_pais($codprograma,$fechai,$fechaf,$auditor,$pais,$oficina,$oficinacontrato,$certificador,$anio,$id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT oficina, COUNT(item) AS total, round(AVG(stiempo),2) as stiempo,
				round(AVG(scalidad),2) as scalidad, round(AVG(sproc),2) as sproc, 
				round(avg((stiempo+scalidad+sproc)/3),2) as promedio
			FROM kpi_importar INNER JOIN kpi_importar_cab ON kpi_importar.id =kpi_importar_cab.id 
				AND	kpi_importar.codprograma =kpi_importar_cab.codprograma
			WHERE flgcerrado='1' AND kpi_importar_cab.flag='1' AND TO_DAYS(fecha)>0  ";
			
		//if($codprograma!='') $sql.= " and kpi_importar.codprograma=$codprograma ";
		if($codprograma!='') $sql.= " and kpi_importar.codprograma in ($codprograma) ";
		if($auditor!='') $sql.= " and (fullauditor like '%".$auditor."%' )";
		if($pais!='') $sql.= " and pais = '$pais' ";
		if($id_pais!='esp') 
			$sql.=" and oficina in (select upper(nombre) from prg_paises where flag='1' and id_pais='$id_pais')";
		if($oficinacontrato!='') $sql.= " and oficinacontrato = '$oficinacontrato' ";
		if($oficina!='') $sql.= " and oficina = '$oficina' ";
		if($certificador!='') $sql.= " and certificador = '$certificador' ";
		if($anio!='') $sql.= " and year(fecha)=$anio";
		if($fechai!='') $sql.= " and to_days(fecha)>= to_days('$fechai')";
		if($fechaf!='') $sql.= " and to_days(fecha) <= to_days('$fechaf')";
		
		$sql.=" GROUP BY oficina 
				order by promedio desc";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;
	}
	
	public function select_kpigraf_pais_cer($codprograma,$fechai,$fechaf,$auditor,$pais,$oficinacontrato,$anio,$id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT certificador as oficina, COUNT(item) AS total, 
				round(AVG(stiempo),2) as stiempo,
				round(AVG(scalidad),2) as scalidad, 
				round(AVG(sgestion),2) as sgestion, 
				round(AVG(svolumen),2) as svolumen, 
				round(avg((ifnull(stiempo,0)+ ifnull(scalidad,0)+ ifnull(sgestion,0)+ ifnull(svolumen,0))/4),2) as promedio
			FROM kpi_importar INNER JOIN kpi_importar_cab ON kpi_importar.id =kpi_importar_cab.id 
				AND	kpi_importar.codprograma =kpi_importar_cab.codprograma
			WHERE flgcerrado='1' AND kpi_importar_cab.flag='1' AND TO_DAYS(fechaevaluacion)>0   and kpi_importar.tipokpi='cer' ";
			
		if($codprograma!='') $sql.= " and kpi_importar.codprograma=$codprograma ";
		if($auditor!='') $sql.= " and (fullauditor like '%".$auditor."%' )";
		if($pais!='') $sql.= " and pais = '$pais' ";
		if($id_pais!='esp') 
			$sql.=" and certificador in (select upper(nombre) from prg_paises where flag='1' and id_pais='$id_pais')";
		if($oficinacontrato!='') $sql.= " and oficinacontrato = '$oficinacontrato' ";
		if($anio!='') $sql.= " and year(fecha)=$anio";
		if($fechai!='') $sql.= " and to_days(fecha)>= to_days('$fechai')";
		if($fechaf!='') $sql.= " and to_days(fecha) <= to_days('$fechaf')";
		
		$sql.=" GROUP BY certificador 
				order by promedio desc";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;
	}
	
	public function select_kpigraf_pais_mes($codprograma,$pais,$auditor,$anio,$oficina,$oficinacontrato,$certificador,$id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT MONTH(fecha) AS mes, COUNT(item) AS total, ROUND(AVG(stiempo),2) AS stiempo,
			ROUND(AVG(scalidad),2) AS scalidad, ROUND(AVG(sproc),2) AS sproc, 
			ROUND(AVG((ifnull(stiempo,0)+ ifnull(scalidad,0)+ ifnull(sproc,0))/3),2) AS promedio
			FROM kpi_importar INNER JOIN kpi_importar_cab ON kpi_importar.id =kpi_importar_cab.id 
			AND	kpi_importar.codprograma =kpi_importar_cab.codprograma
			WHERE flgcerrado='1' AND kpi_importar_cab.flag='1'  AND TO_DAYS(fecha)>0  ";
	
		//if($codprograma!='') $sql.= " AND kpi_importar.codprograma=$codprograma";
		if($codprograma!='') $sql.= " AND kpi_importar.codprograma in ($codprograma)";
		if($auditor!='') $sql.= " and (fullauditor like '%".$auditor."%' )";
		if($anio!='') $sql.= " and year(fecha)=$anio";
		if($pais!='') $sql.= " and pais = '$pais' ";
		if($id_pais!='esp') 
			$sql.=" and oficina in (select upper(nombre) from prg_paises where flag='1' and id_pais='$id_pais')";
		
		if($oficinacontrato!='') $sql.= " and oficinacontrato = '$oficinacontrato' ";
		if($oficina!='') $sql.= " and oficina = '$oficina' ";
		if($certificador!='') $sql.= " and certificador = '$certificador' ";
		
		$sql.=" GROUP BY mes
			ORDER BY year(fecha), month(fecha) ";
		
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;
	}
	
	public function select_kpigraf_pais_mes_cer($codprograma,$pais,$auditor,$anio,$oficinacontrato,$id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT MONTH(fechaevaluacion) AS mes, COUNT(item) AS total,
			round(AVG(stiempo),2) as stiempo,
				round(AVG(scalidad),2) as scalidad, 
				round(AVG(sgestion),2) as sgestion, 
				round(AVG(svolumen),2) as svolumen, 
			round(avg((ifnull(stiempo,0)+ ifnull(scalidad,0) + ifnull(sgestion,) + ifnull(svolumen,0))/4),2) as promedio
			FROM kpi_importar INNER JOIN kpi_importar_cab ON kpi_importar.id =kpi_importar_cab.id 
			AND	kpi_importar.codprograma =kpi_importar_cab.codprograma
			WHERE flgcerrado='1' AND kpi_importar_cab.flag='1'  AND TO_DAYS(fechaevaluacion)>0  and kpi_importar.tipokpi='cer' ";
	
		if($codprograma!='') $sql.= " AND kpi_importar.codprograma=$codprograma";
		if($auditor!='') $sql.= " and (fullauditor like '%".$auditor."%' )";
		if($anio!='') $sql.= " and year(fechaevaluacion)=$anio";
		if($pais!='') $sql.= " and pais = '$pais' ";
		if($id_pais!='esp') 
			$sql.=" and oficina in (select upper(nombre) from prg_paises where flag='1' and id_pais='$id_pais')";
		
		if($oficinacontrato!='') $sql.= " and oficinacontrato = '$oficinacontrato' ";
		
		$sql.=" GROUP BY mes
			ORDER BY year(fechaevaluacion), month(fechaevaluacion) ";
		
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;
	}

	public function select_kpigraf_oneAudit($tipokpi,$auditor,$codprograma,$fechai,$fechaf,$oficina,$anio,$pais,$oficinacontrato){
		unset($this->listas);
		$this->listas=[]; // programa
		$sql="SELECT fullauditor,certificadorname,pais, stiempo,scalidad,svolumen,sgestion,
				sproc,project_id,sfinal,project,
				subprograma,subprograma2,subprograma3,subprograma4,modulo,
				round((stiempo+scalidad+sproc)/3,2) as promedio,
				round((stiempo+scalidad+svolumen+sgestion)/4,2) as promedio_cer,
				date_format(fecha,'%d/%m/%Y') as fechaf,
				date_format(fecha,'%d/%m') as fechaff,
				modulo, kpi_programa.programa,pais,oficina, oficinacontrato,
				date_format(fechaevaluacion,'%d/%m/%Y') as fechaevaluacionf,
				insp_id,certificador,date_format(kpi_importar_cab.fecha_ingreso,'%d/%m/%Y') as fecha_ingresof
				FROM kpi_importar INNER JOIN 
					kpi_importar_cab ON kpi_importar.id =kpi_importar_cab.id 
						AND kpi_importar.codprograma =kpi_importar_cab.codprograma inner join 
					kpi_programa on kpi_importar.codprograma=kpi_programa.codprograma
				WHERE flgcerrado='1' AND kpi_importar_cab.flag='1' and kpi_importar.tipokpi='$tipokpi'	";
					
		if($tipokpi=='aud' and $auditor!='') $sql.= " and (auditor = '$auditor' )";
		if($tipokpi=='cer' and $auditor!='') $sql.= " and (usercertificador = '$auditor' )";
		
		if($pais!='') $sql.= " and pais = '$pais' ";			
		if($codprograma!='') $sql.=" and kpi_importar.codprograma in ($codprograma) ";	
		if($fechai!='') $sql.= " and to_days(fecha) >= to_days('$fechai')";
		if($fechaf!='') $sql.= " and to_days(fecha) <= to_days('$fechaf')";
		if($oficina!='') $sql.= " and oficina = '$oficina' ";
		if($oficinacontrato!='') $sql.= " and oficinacontrato = '$oficinacontrato' ";
		if($anio!='') $sql.= " and year(fecha) = $anio ";

		$sql.= " ORDER BY fecha asc ";

		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;
	}
	
	public function select_kpigraf_oneAuditMes($tipokpi,$auditor,$codprograma,$fechai,$fechaf,$oficina,$anio,$pais,$oficinacontrato){
		unset($this->listas);
		$this->listas=[];
		if($tipokpi=='aud')
			$sql="SELECT MONTH(fecha) AS mes, year(fecha) AS anio, ";
		else if($tipokpi=='cer')
			$sql="SELECT MONTH(fechaevaluacion) AS mes, year(fechaevaluacion) AS anio, ";	
	
		$sql.=" ROUND(AVG(stiempo),2) AS stiempo,
				ROUND(AVG(scalidad),2) AS scalidad, 
				ROUND(AVG(sproc),2) AS sproc, 
				ROUND(AVG(svolumen),2) AS svolumen, 
				ROUND(AVG(sgestion),2) AS sgestion, 
				ROUND(AVG(sfinal),2) AS promedio,
				ROUND(AVG(( ifnull(stiempo,0) + ifnull(scalidad,0) + ifnull(svolumen,0) + ifnull(sgestion,0))/4),2) AS promedio_cer
			FROM kpi_importar INNER JOIN 
				kpi_importar_cab ON kpi_importar.id =kpi_importar_cab.id 
					AND kpi_importar.codprograma =kpi_importar_cab.codprograma inner join 
				kpi_programa on kpi_importar.codprograma=kpi_programa.codprograma
			WHERE flgcerrado='1' AND kpi_importar_cab.flag='1'	";
					
		if($tipokpi=='aud' and $auditor!='') $sql.= " and (auditor = '$auditor' )";
		if($tipokpi=='cer' and $auditor!='') $sql.= " and (usercertificador = '$auditor' )";
		
		if($pais!='') $sql.= " and pais = '$pais' ";			
		if($codprograma!='') $sql.=" and kpi_importar.codprograma in ($codprograma) ";	
		if($fechai!='') $sql.= " and to_days(fecha) >= to_days('$fechai')";
		if($fechaf!='') $sql.= " and to_days(fecha) <= to_days('$fechaf')";
		if($oficina!='') $sql.= " and oficina = '$oficina' ";
		if($oficinacontrato!='') $sql.= " and oficinacontrato = '$oficinacontrato' ";
		if($anio!='') $sql.= " and year(fecha) = $anio ";

		$sql.= " GROUP BY anio,mes ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;
	}
	
	public function select_kpigrafDetalle($campo,$sqlDet,$id_pais,$camponame){
		unset($this->listas);
		$this->listas=[];
		 $sql="SELECT substring($camponame,1,20) as fullauditor,
				round(AVG($campo),2) as valor
				FROM kpi_importar INNER JOIN kpi_importar_cab ON kpi_importar.id =kpi_importar_cab.id 
					AND	kpi_importar.codprograma =kpi_importar_cab.codprograma
				WHERE flgcerrado='1' AND kpi_importar_cab.flag='1' $sqlDet ";
		if($id_pais!='esp') 
			$sql.=" and oficina in (select upper(nombre) from prg_paises where flag='1' and id_pais='$id_pais')";
				
		if($camponame=='fullauditor'){
			$camponame_groupby = 'auditor';
		}else{
			$camponame_groupby = 'usercertificador';
		}
		$sql.=" GROUP BY $camponame_groupby
				order by 2 desc ";

				$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;
	}
	
	
}
?>