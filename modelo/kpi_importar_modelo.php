<?php
class kpi_importar_model{
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

	public function select_kpiimportar($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		$this->listas=null;
		$sql="SELECT *
				from kpi_programa 
				where kpi_programa.flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_emailPrograma($codprograma){
		$sql="SELECT ifnull(emailsuper,'') as emailsuper
				from kpi_programa 
				where codprograma=$codprograma";
	
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
		
	}

	// total de registros 
	public function selec_total_kpiimportar($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			from kpi_programa 
			where kpi_programa.flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function select_kpiformatoxls($codprograma,$id_pais){
		unset($this->listas);
		$sql="SELECT kpi_programa.*,group_concat(concat_ws('=>',i.indicador,pi.peso)) as indicador 
			from kpi_programa left join kpi_programaxindicador pi on kpi_programa.codprograma=pi.codprograma
				left join kpi_indicador i on  pi.codindicador=i.codindicador and i.flag='1'
			where kpi_programa.flag='1' and kpi_programa.id_pais = '$id_pais' 
			and kpi_programa.codprograma=$codprograma
			group by kpi_programa.codprograma
			order by programa";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}	
	
	public function selec_one_kpiprograma($codprograma){
		$sql="SELECT *
			from kpi_programa
			WHERE codprograma=$codprograma ";
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}

	public function select_kpiindicadores($codprograma){
		unset($this->listas);
		$sql="SELECT i.codindicador, i.indicador , peso
			from kpi_indicador i inner join kpi_programaxindicador pi on i.codindicador=pi.codindicador 
			WHERE codprograma=$codprograma ";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

	public function insert_kpiimportar($data_sql,$datadet_sql,$tipokpi){
		$sql="truncate  kpi_importar_log ";
		$consulta=$this->db->execute($sql);
		
		$sql="truncate  kpi_importardet_log ";
		$consulta=$this->db->execute($sql);
		
		if($tipokpi=='aud'){
			$sql="insert into kpi_importar_log(id,item,codprograma,tipokpi,project_id,project,insp_id,tipo,fechadraft,codigoaud,auditor,pais,fecha,certificador,oficina,oficinacontrato,programa,subprograma,subprograma2,
					subprograma3,subprograma4,modulo,fechaevaluacion,evaluador,usercertificador) values $data_sql ";
				
		}else if($tipokpi=='cer')
			$sql="insert into kpi_importar_log(id,item,codprograma,tipokpi,certificador,oficina,programa,subprograma,subprograma2,
					subprograma3,subprograma4,modulo,fechaevaluacion,certificadorname,usercertificador,evaluador,userevaluador) values $data_sql ";
		
		$consulta=$this->db->execute($sql);
		
		$sql="insert into kpi_importardet_log(id,item,codindicador,valor) values $datadet_sql ";			
		$consulta=$this->db->execute($sql);
		
		return $consulta;	
	}

	//*************************************************
	// para las validaciones de importar excel 

	public function select_kpiauditorvacio(){
		unset($this->listas);
		$sql="UPDATE kpi_importar_log SET auditor='', project_id=trim(project_id), insp_id=trim(insp_id)";
		$consulta=$this->db->execute($sql);
	
		$sql="UPDATE kpi_importar_log INNER JOIN 
				(
				SELECT u.usuario, CONCAT_WS(' ',a.nombre,a.apepaterno, a.apematerno) AS nombres
				FROM prg_auditor a INNER JOIN prg_usuarios u ON a.id_auditor=u.id_auditor
				WHERE a.flag='1' AND u.flag='1'
				UNION
				SELECT codigo AS usuario, nombres FROM kpi_auditor WHERE flag='1'
				) AS vista ON kpi_importar_log.codigoaud=vista.usuario
				SET kpi_importar_log.auditor=vista.nombres";
		$consulta=$this->db->execute($sql);
	
		$sql="select group_concat(item separator ', ') as item, count(*) as total  
					FROM kpi_importar_log
					WHERE ifnull(codigoaud,'')='' or ifnull(auditor,'')=''";
		$consulta=$this->db->consultarOne($sql);		
		
		$sql="delete FROM kpi_importar_log WHERE ifnull(codigoaud,'')='' or ifnull(auditor,'')=''";
		$this->db->execute($sql);
		
        return $consulta;	
		
	}
	
	public function select_kpicertivacio(){
		unset($this->listas);
		$sql="UPDATE kpi_importar_log SET certificadorname='',evaluador='' ";
		$consulta=$this->db->execute($sql);
	
		$sql="UPDATE kpi_importar_log INNER JOIN 
				(
				SELECT u.usuario, CONCAT_WS(' ',a.nombre,a.apepaterno, a.apematerno) AS nombres
				FROM prg_auditor a INNER JOIN prg_usuarios u ON a.id_auditor=u.id_auditor
				WHERE a.flag='1' AND u.flag='1'
				UNION
				SELECT codigo AS usuario, nombres FROM kpi_auditor WHERE flag='1'
				) AS vista ON kpi_importar_log.usercertificador=vista.usuario
				SET kpi_importar_log.certificadorname=vista.nombres";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE kpi_importar_log INNER JOIN 
				(
				SELECT u.usuario, CONCAT_WS(' ',a.nombre,a.apepaterno, a.apematerno) AS nombres
				FROM prg_auditor a INNER JOIN prg_usuarios u ON a.id_auditor=u.id_auditor
				WHERE a.flag='1' AND u.flag='1'
				UNION
				SELECT codigo AS usuario, nombres FROM kpi_auditor WHERE flag='1'
				) AS vista ON kpi_importar_log.userevaluador=vista.usuario
				SET kpi_importar_log.evaluador=vista.nombres";
		$consulta=$this->db->execute($sql);
	
		$sql="select group_concat(item separator ', ') as item, count(*) as total  
					FROM kpi_importar_log
					WHERE ifnull(userevaluador,'')='' or ifnull(usercertificador,'')=''";
		$consulta=$this->db->consultarOne($sql);		
		
		$sql="delete FROM kpi_importar_log WHERE ifnull(userevaluador,'')='' or ifnull(usercertificador,'')=''";
		$this->db->execute($sql);
		
        return $consulta;	
		
	}
	
	public function select_kpicolumnaentero(){
		unset($this->listas);
			
		$sql="SELECT group_concat(item separator ', ') as item, count(*) as total 
			FROM kpi_importar_log WHERE project_id NOT REGEXP '^[0-9]+$' ";
		$consulta=$this->db->consultarOne($sql);		
		
		$sql="delete FROM kpi_importar_log WHERE project_id NOT REGEXP '^[0-9]+$' ";
		$this->db->execute($sql);
		
        return $consulta;	
		
	}
	
	public function select_kpicolumnainspentero(){
		unset($this->listas);
			
		$sql="SELECT group_concat(item separator ', ') as item, count(*) as total 
			FROM kpi_importar_log WHERE insp_id NOT REGEXP '^[0-9]+$' ";
		$consulta=$this->db->consultarOne($sql);		
		
		$sql="delete FROM kpi_importar_log WHERE insp_id NOT REGEXP '^[0-9]+$' ";
		$this->db->execute($sql);
		
        return $consulta;	
		
	}
	
	public function select_kpivaloresvacio(){
		unset($this->listas);
			
		$sql="SELECT GROUP_CONCAT(DISTINCT item SEPARATOR ', ') AS item, COUNT(DISTINCT item) AS total 
					FROM kpi_importardet_log l 
					WHERE l.valor NOT REGEXP '^(-|\\\\+){0,1}([0-9]+\\\\.[0-9]*|[0-9]*\\\\.[0-9]+|[0-9]+)$' 
					AND l.codindicador>0";
		$consulta=$this->db->consultarOne($sql);		
		
		if(!empty($consulta)){
			$ColsValores=$consulta['item'];
			$sql="delete FROM kpi_importar_log WHERE  item in ($ColsValores)";
			$this->db->execute($sql);
		}
        return $consulta;	
		
	}
	
	public function select_columnasvacio(){
		unset($this->listas);
			
		$sql="SELECT GROUP_CONCAT(item separator ', ') AS item, COUNT(*) AS total 
					FROM kpi_importar_log WHERE project_id ='' OR project='' OR insp_id='' OR tipo='' 
						OR fechadraft='' OR codigoaud='' OR auditor='' OR pais='' OR fecha='' OR certificador=''
						OR oficina='' OR oficinacontrato='' OR programa='' OR modulo='' OR fechaevaluacion='' 
						OR evaluador='' OR usercertificador='' ";
		$consulta=$this->db->consultarOne($sql);		
		
		if(!empty($consulta)){
			$sql="delete FROM kpi_importar_log 
					WHERE project_id ='' OR project='' OR insp_id='' OR tipo='' 
					OR fechadraft='' OR codigoaud='' OR auditor='' OR pais='' OR fecha='' OR certificador=''
					OR oficina='' OR oficinacontrato='' OR programa='' OR modulo='' OR fechaevaluacion='' 
					OR evaluador='' OR usercertificador=''";
			$this->db->execute($sql);
		}
        return $consulta;	
		
	}
	
	public function select_kpiduplicados($codprograma){
		
		$sql="select flgvalida from kpi_programa where codprograma=$codprograma";
		$consulta=$this->db->consultarOne($sql);
		$flgvalida=$consulta['flgvalida'];
		
		if($flgvalida=='1'){
			$sql="select GROUP_CONCAT(item separator ', ') AS item, COUNT(*) AS total FROM kpi_importar_log
					WHERE CONCAT_WS('_',TO_DAYS(fecha),auditor,modulo,project_id,codprograma,insp_id) IN (
						SELECT CONCAT_WS('_',TO_DAYS(fecha),fullauditor,modulo,project_id,codprograma,insp_id) 
						FROM kpi_importar
					) ";
			$consulta=$this->db->consultarOne($sql);		
			
			if(!empty($consulta)){
				$sql="delete FROM kpi_importar_log
						WHERE CONCAT_WS('_',TO_DAYS(fecha),auditor,modulo,project_id,codprograma,insp_id) IN (
							SELECT CONCAT_WS('_',TO_DAYS(fecha),fullauditor,modulo,project_id,codprograma,insp_id) 
							FROM kpi_importar
						)";
				$this->db->execute($sql);
			}
		}else{
			$sql="select GROUP_CONCAT(item separator ', ') AS item, COUNT(*) AS total FROM kpi_importar_log
					WHERE CONCAT_WS('_',TO_DAYS(fecha),auditor,modulo,project_id,codprograma) IN (
						SELECT CONCAT_WS('_',TO_DAYS(fecha),fullauditor,modulo,project_id,codprograma) 
						FROM kpi_importar
					) ";
			$consulta=$this->db->consultarOne($sql);		
			
			if(!empty($consulta)){
				$sql="delete FROM kpi_importar_log
						WHERE CONCAT_WS('_',TO_DAYS(fecha),auditor,modulo,project_id,codprograma) IN (
							SELECT CONCAT_WS('_',TO_DAYS(fecha),fullauditor,modulo,project_id,codprograma) 
							FROM kpi_importar
						)";
				$this->db->execute($sql);
			}
				
		}
		
		
        return $consulta;
		
	}
	
	public function select_kpinoespais(){
		$sql="SELECT GROUP_CONCAT(DISTINCT item SEPARATOR ', ') AS item, COUNT(DISTINCT item) AS total 
					FROM 
					(
					SELECT item FROM kpi_importar_log 
					WHERE pais NOT IN (SELECT CONVERT(nombre,CHAR) FROM t_mae_pais WHERE flag='1')
					UNION
					SELECT item FROM kpi_importar_log 
					WHERE oficina NOT IN (SELECT CONVERT(nombre,CHAR) FROM t_mae_pais WHERE flag='1')
					UNION
					SELECT item FROM kpi_importar_log 
					WHERE oficinacontrato NOT IN (SELECT CONVERT(nombre,CHAR) FROM t_mae_pais WHERE flag='1')
					) AS vista ";
		$consulta=$this->db->consultarOne($sql);		
		
		if(!empty($consulta)){
			$sql="delete FROM kpi_importar_log
					WHERE item in (
					select item from (
						SELECT item FROM kpi_importar_log 
						WHERE pais NOT IN (SELECT CONVERT(nombre,CHAR) FROM t_mae_pais WHERE flag='1')
						UNION
						SELECT item FROM kpi_importar_log 
						WHERE oficina NOT IN (SELECT CONVERT(nombre,CHAR) FROM t_mae_pais WHERE flag='1')
						UNION
						SELECT item FROM kpi_importar_log 
						WHERE oficinacontrato NOT IN (SELECT CONVERT(nombre,CHAR) FROM t_mae_pais WHERE flag='1')
					) as vista
					)";
			$this->db->execute($sql);
		}
        return $consulta;
		
	}

	public function select_kpiinconsistencia(){
		unset($this->listas);
		 $sql="select COUNT(*) AS total FROM kpi_importar_log";
	
		$consulta=$this->db->consultarOne($sql);	
        return $consulta;	
		
	}
	
	// para las validaciones de importar excel 
	//*************************************************
	
	public function procesa_uploadKpiDatos($codprograma,$nombrefile,$seconds,$usuario,$ip,$id_pais,$tipokpi){
		$sql="insert into kpi_importar_cab(id,codprograma,id_pais, nombrefile, usuario_ingreso,fecha_ingreso,ip_ingreso) 
		values($seconds,$codprograma,'$id_pais','$nombrefile','$usuario',now(),'$ip') ";
		echo $sql;
		$consulta=$this->db->execute($sql);
		
		
		$sql="select flgvalida from kpi_programa where codprograma=$codprograma";
		$consulta=$this->db->consultarOne($sql);
		$flgvalida=$consulta['flgvalida'];
		
		if($tipokpi=='aud'){
			if($flgvalida=='1')
				$sql="DELETE FROM kpi_importar_log
				WHERE CONCAT_WS('_',TO_DAYS(fecha),auditor,modulo,project_id,codprograma,insp_id) IN (
					SELECT CONCAT_WS('_',TO_DAYS(fecha),fullauditor,modulo,project_id,codprograma,insp_id) FROM kpi_importar
				)";
			else
				$sql="DELETE FROM kpi_importar_log
				WHERE CONCAT_WS('_',TO_DAYS(fecha),auditor,modulo,project_id,codprograma) IN (
					SELECT CONCAT_WS('_',TO_DAYS(fecha),fullauditor,modulo,project_id,codprograma) FROM kpi_importar
				)";
		}else if($tipokpi=='cer'){
			$sql="DELETE FROM kpi_importar_log
				WHERE CONCAT_WS('_',TO_DAYS(fechaevaluacion),usercertificador,codprograma) IN (
					SELECT CONCAT_WS('_',TO_DAYS(fechaevaluacion),usercertificador,codprograma) FROM kpi_importar
				)";
		}		
		
		$consulta=$this->db->execute($sql);
		
		if($tipokpi=='aud')
			$campo='codigoaud';
		else if($tipokpi=='cer')
			$campo='evaluador';
		
		$sql="insert into kpi_importar(id,item,codprograma ,project_id ,project ,insp_id ,tipo,fechadraft,
		auditor,fullauditor ,pais,fecha ,certificador ,oficina ,oficinacontrato,programa ,subprograma,
		subprograma2,subprograma3,subprograma4,modulo, fechaevaluacion ,fullevaluador,usercertificador,
		certificadorname,userevaluador,tipokpi) 
		select * from kpi_importar_log where id=$seconds and ifnull($campo,'')!=''";
		echo $sql;
		$consulta=$this->db->execute($sql);
		
		$sql="insert into kpi_importardet select * from kpi_importardet_log where id=$seconds ";			
		echo $sql;
		$consulta=$this->db->execute($sql);
		
		$sql="delete from  kpi_importardet_log ";		
		$consulta=$this->db->execute($sql);
		
		//$sql="truncate kpi_importar_log";		
		//$consulta=$this->db->execute($sql);

		$sql="UPDATE kpi_importar INNER JOIN prg_usuarios ON kpi_importar.auditor=prg_usuarios.usuario
			SET kpi_importar.codauditor=prg_usuarios.id_auditor
			where id=$seconds ";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE kpi_importar INNER JOIN prg_usuarios ON kpi_importar.usercertificador=prg_usuarios.usuario
			SET kpi_importar.id_certificador=prg_usuarios.id_auditor
			where id=$seconds ";
		$consulta=$this->db->execute($sql);

		$sql="UPDATE kpi_importar 
			SET pais=upper(pais),  oficina=upper(oficina), oficinacontrato=upper(oficinacontrato)
			where id=$seconds ";
		$consulta=$this->db->execute($sql);

		return $consulta;	
	}
	
	public function select_kpitemporal($codprograma,$seconds,$id_pais,$tipokpi){
		$this->listas=null;
		$sql="select flgvalida from kpi_programa where codprograma=$codprograma";
		$consulta=$this->db->consultarOne($sql);
		$flgvalida=$consulta['flgvalida'];
		
		if($tipokpi=='aud')
			$campo='codigoaud';
		else if($tipokpi=='cer')
			$campo='evaluador';
		
		if($flgvalida=='1')
			$sql="select * from kpi_importar_log where id=$seconds and ifnull($campo,'')!=''
				and CONCAT_WS('_',TO_DAYS(fecha),auditor,modulo,project_id,codprograma,insp_id) not IN (
					SELECT CONCAT_WS('_',TO_DAYS(fecha),fullauditor,modulo,project_id,codprograma,insp_id) FROM kpi_importar
				)";
		else
			$sql="select * from kpi_importar_log where id=$seconds and ifnull($campo,'')!=''
				AND CONCAT_WS('_',TO_DAYS(fechaevaluacion),userevaluador,modulo,codprograma)NOT IN (
					SELECT CONCAT_WS('_',TO_DAYS(fechaevaluacion),userevaluador,modulo,codprograma) FROM kpi_importar WHERE tipokpi='cer'
				)";
		
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}
	
	public function select_kpianalisis($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		
		$sql="SELECT *, date_format(fecha_ingreso,'%d/%m/%Y %H:%i:%s') as fechaf,
				case flgcerrado when '1' then 'Cerrado' else 'Sin procesar' end as dsccerrado
				from kpi_importar_cab 
				where flag='1'  $searchQuery ";
		$sql.=" order by  ".$columnName." ".$columnSortOrder."	 limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
		
        return $this->listas;	
		
	}

	// total de registros 
	public function selec_total_kpianalisis($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			from kpi_importar_cab 
			where flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function delete_detetDatosKpi($idt,$codprograma){
		$sql="UPDATE kpi_importar_cab SET flag='0'  WHERE id=$idt and codprograma=$codprograma";		
		$consulta=$this->db->execute($sql);
		
		$sql="delete from kpi_importar WHERE id=$idt and codprograma=$codprograma";
		$consulta=$this->db->execute($sql);
		
		$sql="delete from kpi_importar_indicador WHERE id=$idt and codprograma=$codprograma";
		$consulta=$this->db->execute($sql);

		return $consulta;	
	}
	
	// pantalla de analisis de datos y resulatdos
	
	public function select_kpianalisisCab($idt,$codprograma){
		$sql="SELECT * from kpi_importar_cab where id='$idt' and codprograma=$codprograma";
	
		$consulta=$this->db->consultarOne($sql);
		return $consulta;
	}
	
	public function select_kpianalisisIndicador($codprograma,$id_pais){
		$sql="SELECT kpi_programa.*,group_concat(concat_ws('=>',i.indicador,pi.peso,i.codindicador)) as indicador 
		from kpi_programa left join kpi_programaxindicador pi on kpi_programa.codprograma=pi.codprograma
			left join kpi_indicador i on  pi.codindicador=i.codindicador and i.flag='1'
		where kpi_programa.flag='1' and kpi_programa.id_pais = '$id_pais' 
		and kpi_programa.codprograma=$codprograma
		group by kpi_programa.codprograma
		order by programa";
	
		$consulta=$this->db->consultarOne($sql);
		return $consulta;
	}
	
	public function select_kpianalisisDet($idt){
		unset($this->listas);
		$sql="SELECT * 	from kpi_importardet where id=$idt ";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function select_kpianalisisAccion($tipokpi,$id_pais){
		unset($this->listas);
		$sql="SELECT * 	from kpi_accion where flag='1' and tipokpi='$tipokpi' and id_pais='$id_pais'";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function select_kpianalisisDatos($codprograma,$idt){
		unset($this->listas);
		$this->listas=[];
		 $sql="SELECT  i.item, ROUND(SUM(kpi_importardet.valor*pxi.peso)/SUM(pxi.peso),2) AS score, 
			kpi_indicador.codcategoria,SUM(pxi.peso) as peso,
			ROUND(SUM(kpi_importardet.valor*pxi.peso),2) as score2
		FROM 	kpi_importar i INNER JOIN 
			kpi_importardet ON i.id=kpi_importardet.id AND i.item=kpi_importardet.item INNER JOIN 
			kpi_programaxindicador pxi ON kpi_importardet.codindicador=pxi.codindicador AND i.codprograma=pxi.codprograma INNER JOIN
			kpi_indicador ON pxi.codindicador=kpi_indicador.codindicador
		WHERE i.id='$idt' AND i.codprograma=$codprograma
		GROUP BY i.id=kpi_importardet.id, kpi_importardet.item,kpi_indicador.codcategoria";
	
		$consulta=$this->db->consultar($sql);	
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	public function select_kpianalisisDatosValor($idt){
		unset($this->listas);
			 // valores score
		$sql="SELECT  * 	FROM 	kpi_importardet WHERE id='$idt' and codindicador=0";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function select_kpianalisisImportar($idt,$auditorcode=null){
		unset($this->listas);
		
		$sql="SELECT *, date_format(fechadraft,'%d.%m.%Y') as fechadraftf, 
				date_format(fecha,'%d.%m.%Y') as fechaf,
				date_format(fechaevaluacion,'%d.%m.%Y') as fechaevalf
				from kpi_importar 
				where id = '$idt' ";
		
		if(!empty($auditorcode)) $sql.=" and auditor='$auditorcode' ";		
		
		$sql.="	order by item";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}

	public function procesa_OkImportaKpi($idt,$codprograma,$id_pais){
		$sql="UPDATE 	kpi_importar i INNER JOIN
			(SELECT  i.item, ROUND(SUM(kpi_importardet.valor*pxi.peso)/SUM(pxi.peso),2) AS score, 
					kpi_indicador.codcategoria,SUM(pxi.peso) AS peso,
					ROUND(SUM(kpi_importardet.valor*pxi.peso),2) AS score2,
					i.id, i.codprograma
				FROM 	kpi_importar i INNER JOIN 
					kpi_importardet ON i.id=kpi_importardet.id AND i.item=kpi_importardet.item INNER JOIN 
					kpi_programaxindicador pxi ON kpi_importardet.codindicador=pxi.codindicador AND i.codprograma=pxi.codprograma INNER JOIN
					kpi_indicador ON pxi.codindicador=kpi_indicador.codindicador
				WHERE i.id='$idt' AND i.codprograma=$codprograma
				GROUP BY i.id=kpi_importardet.id, kpi_importardet.item,kpi_indicador.codcategoria
			) AS vista ON i.item=vista.item AND i.codprograma=vista.codprograma AND i.id=vista.id 
		SET i.scalidad=vista.score
		WHERE  vista.codcategoria='C';";	
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE 	kpi_importar i INNER JOIN
			(SELECT  i.item, ROUND(SUM(kpi_importardet.valor*pxi.peso)/SUM(pxi.peso),2) AS score, 
					kpi_indicador.codcategoria,SUM(pxi.peso) AS peso,
					ROUND(SUM(kpi_importardet.valor*pxi.peso),2) AS score2,
					i.id, i.codprograma
				FROM 	kpi_importar i INNER JOIN 
					kpi_importardet ON i.id=kpi_importardet.id AND i.item=kpi_importardet.item INNER JOIN 
					kpi_programaxindicador pxi ON kpi_importardet.codindicador=pxi.codindicador AND i.codprograma=pxi.codprograma INNER JOIN
					kpi_indicador ON pxi.codindicador=kpi_indicador.codindicador
				WHERE i.id='$idt' AND i.codprograma=$codprograma
				GROUP BY i.id=kpi_importardet.id, kpi_importardet.item,kpi_indicador.codcategoria
			) AS vista ON i.item=vista.item AND i.codprograma=vista.codprograma AND i.id=vista.id 
		SET i.stiempo=vista.score
		WHERE  vista.codcategoria='T';";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE 	kpi_importar i INNER JOIN
			(SELECT  i.item, ROUND(SUM(kpi_importardet.valor*pxi.peso)/SUM(pxi.peso),2) AS score, 
					kpi_indicador.codcategoria,SUM(pxi.peso) AS peso,
					ROUND(SUM(kpi_importardet.valor*pxi.peso),2) AS score2,
					i.id, i.codprograma
				FROM 	kpi_importar i INNER JOIN 
					kpi_importardet ON i.id=kpi_importardet.id AND i.item=kpi_importardet.item INNER JOIN 
					kpi_programaxindicador pxi ON kpi_importardet.codindicador=pxi.codindicador AND i.codprograma=pxi.codprograma INNER JOIN
					kpi_indicador ON pxi.codindicador=kpi_indicador.codindicador
				WHERE i.id='$idt' AND i.codprograma=$codprograma
				GROUP BY i.id=kpi_importardet.id, kpi_importardet.item,kpi_indicador.codcategoria
			) AS vista ON i.item=vista.item AND i.codprograma=vista.codprograma AND i.id=vista.id 
		SET i.sproc=vista.score
		WHERE  vista.codcategoria='P';";
		$consulta=$this->db->execute($sql);

		$sql="UPDATE 	kpi_importar i INNER JOIN
			(SELECT  i.item, 
					
					ROUND(SUM(kpi_importardet.valor*pxi.peso),2) AS score2,
					i.id, i.codprograma
				FROM 	kpi_importar i INNER JOIN 
					kpi_importardet ON i.id=kpi_importardet.id AND i.item=kpi_importardet.item INNER JOIN 
					kpi_programaxindicador pxi ON kpi_importardet.codindicador=pxi.codindicador AND i.codprograma=pxi.codprograma INNER JOIN
					kpi_indicador ON pxi.codindicador=kpi_indicador.codindicador
				WHERE i.id='$idt' AND i.codprograma=$codprograma
				GROUP BY i.id=kpi_importardet.id, kpi_importardet.item
			) AS vista ON i.item=vista.item AND i.codprograma=vista.codprograma AND i.id=vista.id 
		SET i.sfinal=vista.score2;";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE kpi_importar i INNER JOIN kpi_accion ON sfinal>=minimo AND sfinal<=maximo
		SET i.resultado=kpi_accion.valor
		WHERE i.id='$idt' and kpi_accion.id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE kpi_importar i INNER JOIN kpi_importardet d ON i.id=d.id AND i.item=d.item
		SET i.resultado='', sfinal=0,sproc=0,stiempo=0,scalidad=0
		WHERE i.id='$idt' AND d.codindicador=0 AND d.flag='1' AND d.valor='0' AND i.codprograma=$codprograma;";
		$consulta=$this->db->execute($sql);
		
		
		$sql="delete from kpi_importar_indicador where codprograma=$codprograma and id='$idt'";
		$consulta=$this->db->execute($sql);
		
		$sql="INSERT INTO kpi_importar_indicador
		SELECT i.indicador,pi.peso,i.codindicador, '$idt' AS id, pi.codprograma
		FROM kpi_programaxindicador pi 
			LEFT JOIN kpi_indicador i ON  pi.codindicador=i.codindicador AND i.flag='1'
		WHERE pi.codprograma=$codprograma;";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE kpi_importar_cab SET flgcerrado='1' WHERE id='$idt' AND codprograma=$codprograma;";
		$consulta=$this->db->execute($sql);
		
		return $consulta;	
	}
	
	public function procesa_OkImportaKpi_cer($idt,$codprograma){
		$sql="UPDATE 	kpi_importar i INNER JOIN
			(SELECT  i.item, ROUND(SUM(kpi_importardet.valor*pxi.peso)/SUM(pxi.peso),2) AS score, 
					kpi_indicador.codcategoria,SUM(pxi.peso) AS peso,
					ROUND(SUM(kpi_importardet.valor*pxi.peso),2) AS score2,
					i.id, i.codprograma
				FROM 	kpi_importar i INNER JOIN 
					kpi_importardet ON i.id=kpi_importardet.id AND i.item=kpi_importardet.item INNER JOIN 
					kpi_programaxindicador pxi ON kpi_importardet.codindicador=pxi.codindicador AND i.codprograma=pxi.codprograma INNER JOIN
					kpi_indicador ON pxi.codindicador=kpi_indicador.codindicador
				WHERE i.id='$idt' AND i.codprograma=$codprograma
				GROUP BY i.id=kpi_importardet.id, kpi_importardet.item,kpi_indicador.codcategoria
			) AS vista ON i.item=vista.item AND i.codprograma=vista.codprograma AND i.id=vista.id 
		SET i.scalidad=vista.score
		WHERE  vista.codcategoria='Ca';";	
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE 	kpi_importar i INNER JOIN
			(SELECT  i.item, ROUND(SUM(kpi_importardet.valor*pxi.peso)/SUM(pxi.peso),2) AS score, 
					kpi_indicador.codcategoria,SUM(pxi.peso) AS peso,
					ROUND(SUM(kpi_importardet.valor*pxi.peso),2) AS score2,
					i.id, i.codprograma
				FROM 	kpi_importar i INNER JOIN 
					kpi_importardet ON i.id=kpi_importardet.id AND i.item=kpi_importardet.item INNER JOIN 
					kpi_programaxindicador pxi ON kpi_importardet.codindicador=pxi.codindicador AND i.codprograma=pxi.codprograma INNER JOIN
					kpi_indicador ON pxi.codindicador=kpi_indicador.codindicador
				WHERE i.id='$idt' AND i.codprograma=$codprograma
				GROUP BY i.id=kpi_importardet.id, kpi_importardet.item,kpi_indicador.codcategoria
			) AS vista ON i.item=vista.item AND i.codprograma=vista.codprograma AND i.id=vista.id 
		SET i.stiempo=vista.score
		WHERE  vista.codcategoria='Ti';";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE 	kpi_importar i INNER JOIN
			(SELECT  i.item, ROUND(SUM(kpi_importardet.valor*pxi.peso)/SUM(pxi.peso),2) AS score, 
					kpi_indicador.codcategoria,SUM(pxi.peso) AS peso,
					ROUND(SUM(kpi_importardet.valor*pxi.peso),2) AS score2,
					i.id, i.codprograma
				FROM 	kpi_importar i INNER JOIN 
					kpi_importardet ON i.id=kpi_importardet.id AND i.item=kpi_importardet.item INNER JOIN 
					kpi_programaxindicador pxi ON kpi_importardet.codindicador=pxi.codindicador AND i.codprograma=pxi.codprograma INNER JOIN
					kpi_indicador ON pxi.codindicador=kpi_indicador.codindicador
				WHERE i.id='$idt' AND i.codprograma=$codprograma
				GROUP BY i.id=kpi_importardet.id, kpi_importardet.item,kpi_indicador.codcategoria
			) AS vista ON i.item=vista.item AND i.codprograma=vista.codprograma AND i.id=vista.id 
		SET i.sgestion=vista.score
		WHERE  vista.codcategoria='Ge';";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE 	kpi_importar i INNER JOIN
			(SELECT  i.item, ROUND(SUM(kpi_importardet.valor*pxi.peso)/SUM(pxi.peso),2) AS score, 
					kpi_indicador.codcategoria,SUM(pxi.peso) AS peso,
					ROUND(SUM(kpi_importardet.valor*pxi.peso),2) AS score2,
					i.id, i.codprograma
				FROM 	kpi_importar i INNER JOIN 
					kpi_importardet ON i.id=kpi_importardet.id AND i.item=kpi_importardet.item INNER JOIN 
					kpi_programaxindicador pxi ON kpi_importardet.codindicador=pxi.codindicador AND i.codprograma=pxi.codprograma INNER JOIN
					kpi_indicador ON pxi.codindicador=kpi_indicador.codindicador
				WHERE i.id='$idt' AND i.codprograma=$codprograma
				GROUP BY i.id=kpi_importardet.id, kpi_importardet.item,kpi_indicador.codcategoria
			) AS vista ON i.item=vista.item AND i.codprograma=vista.codprograma AND i.id=vista.id 
		SET i.svolumen=vista.score
		WHERE  vista.codcategoria='Vo';";
		$consulta=$this->db->execute($sql);

		$sql="UPDATE 	kpi_importar i INNER JOIN
			(SELECT  i.item, 
					
					ROUND(SUM(kpi_importardet.valor*pxi.peso),2) AS score2,
					i.id, i.codprograma
				FROM 	kpi_importar i INNER JOIN 
					kpi_importardet ON i.id=kpi_importardet.id AND i.item=kpi_importardet.item INNER JOIN 
					kpi_programaxindicador pxi ON kpi_importardet.codindicador=pxi.codindicador AND i.codprograma=pxi.codprograma INNER JOIN
					kpi_indicador ON pxi.codindicador=kpi_indicador.codindicador
				WHERE i.id='$idt' AND i.codprograma=$codprograma
				GROUP BY i.id=kpi_importardet.id, kpi_importardet.item
			) AS vista ON i.item=vista.item AND i.codprograma=vista.codprograma AND i.id=vista.id 
		SET i.sfinal=vista.score2;";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE kpi_importar i INNER JOIN kpi_accion ON sfinal>=minimo AND sfinal<=maximo
		SET i.resultado=kpi_accion.valor
		WHERE i.id='$idt' and kpi_accion.id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE kpi_importar i INNER JOIN kpi_importardet d ON i.id=d.id AND i.item=d.item
		SET i.resultado='', sfinal=0,sproc=0,stiempo=0,scalidad=0
		WHERE i.id='$idt' AND d.codindicador=0 AND d.flag='1' AND d.valor='0' AND i.codprograma=$codprograma;";
		$consulta=$this->db->execute($sql);
		
		
		$sql="delete from kpi_importar_indicador where codprograma=$codprograma and id='$idt'";
		$consulta=$this->db->execute($sql);
		
		$sql="INSERT INTO kpi_importar_indicador
		SELECT i.indicador,pi.peso,i.codindicador, '$idt' AS id, pi.codprograma
		FROM kpi_programaxindicador pi 
			LEFT JOIN kpi_indicador i ON  pi.codindicador=i.codindicador AND i.flag='1'
		WHERE pi.codprograma=$codprograma;";
		$consulta=$this->db->execute($sql);
		
		$sql="UPDATE kpi_importar_cab SET flgcerrado='1' WHERE id='$idt' AND codprograma=$codprograma;";
		$consulta=$this->db->execute($sql);
		
		return $consulta;	
	}
	
	
	public function select_auditorByEmail($idt,$codprograma,$id_pais){
		unset($this->listas);
		$this->listas=[];
		 $sql="SELECT auditor, CONCAT_WS(' ',nombre,apepaterno,apematerno) AS auditorname, email
				FROM kpi_importar i  INNER JOIN prg_auditor a ON  i.codauditor=a.id_auditor
				WHERE id='$idt'
				UNION
				SELECT auditor, nombres AS auditorname, email
				FROM kpi_importar i  INNER JOIN kpi_auditor a ON i.auditor=a.codigo 
				WHERE id='$idt' AND i.codauditor IS NULL";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function select_certificadorByEmail($idt,$codprograma,$id_pais){
		unset($this->listas);
		$this->listas="";
		 $sql="SELECT auditor, CONCAT_WS(' ',nombre,apepaterno,apematerno) AS auditorname, email
				FROM kpi_importar i  INNER JOIN prg_auditor a ON  i.id_certificador=a.id_auditor
				WHERE id='$idt'
				UNION
				SELECT auditor, nombres AS auditorname, email
				FROM kpi_importar i  INNER JOIN kpi_auditor a ON i.usercertificador=a.codigo 
				WHERE id='$idt' AND i.id_certificador IS NULL";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}

	public function delete_analisisKpiImportar($idt,$item,$tipoKpi){
		$sql="delete from kpi_importar WHERE id='$idt' and item='$item' and tipokpi='$tipoKpi'";
		$consulta=$this->db->execute($sql);

		return $consulta;	
	}

}
?>