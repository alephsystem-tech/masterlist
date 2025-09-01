<?php
class lst_solicitud_model{
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

	public function select_solicitud($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		
		$sql="SELECT distinct s.* , DATE_FORMAT(s.fecha,'%d/%m/%Y') AS fecha_f,
				IFNULL(agricultor.cantidad,0) AS sumagricultor,
				IFNULL(compra.cantidad,0) AS sumcompra,
				IFNULL(proventa.cantidad,0) AS sumprov,
				CASE estado 
					WHEN 'a' THEN 'Aprobado' 
					WHEN 'e' THEN 'Enviado' 
					WHEN 'd' THEN 'Desaprobado' 
					ELSE 'Pendiente' END AS dscestado,
				p.proyect as proyecto,
				tc.tipocertificado,
				c.cultivo,
				lst_semanas.nombre as semanadsc,
				li.tipo,
				p.country as pais
				FROM lst_solicitud s 
					LEFT join lst_listaintegrada li on s.codlista=li.codlista
					LEFT join lst_tipocertificado tc on s.codtipocertificado=tc.codtipocertificado
					LEFT join lst_cultivo c on s.codcultivo=c.codcultivo
					LEFT join lst_semanas on s.semana=lst_semanas.id_semana
					LEFT JOIN
					prg_proyecto p on s.project_id=p.project_id and s.id_pais=p.id_pais left join
					(SELECT codsolicitud, SUM(IFNULL(empaque*kgxempaque,0)) AS cantidad 
						FROM lst_solicitud_det 
						WHERE flag='1'
						GROUP BY codsolicitud) AS agricultor
					ON s.codsolicitud=agricultor.codsolicitud	LEFT JOIN
					(SELECT codsolicitud, SUM(IFNULL(kgxempaque,0)) AS cantidad 
						FROM lst_solicitud_com 
						WHERE flag='1'
						GROUP BY codsolicitud) AS compra
					ON s.codsolicitud=compra.codsolicitud LEFT JOIN
					(SELECT codsolicitud, SUM(IFNULL(kgxempaque*cantidad,0)) AS cantidad 
					FROM lst_proveedorventa 
					WHERE flag='1'
					GROUP BY codsolicitud) AS proventa
				 ON s.codsolicitud=proventa.codsolicitud
				 
				WHERE s.flag='1' /*and s.estado!='p'*/ and s.numero is not null   $searchQuery ";
    
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	
	// total de registros por auditor fecha
	public function selec_total_solicitud($searchQuery=null){
		$sql=" SELECT COUNT(s.codsolicitud) AS total 
				FROM lst_solicitud s 
					LEFT join lst_listaintegrada li on s.codlista=li.codlista
					inner join lst_tipocertificado tc on s.codtipocertificado=tc.codtipocertificado
					inner join lst_cultivo c on s.codcultivo=c.codcultivo
					inner join lst_semanas on s.semana=lst_semanas.id_semana
					LEFT JOIN
					prg_proyecto p on s.project_id=p.project_id and s.id_pais=p.id_pais left join
					(SELECT codsolicitud, SUM(IFNULL(empaque*kgxempaque,0)) AS cantidad 
						FROM lst_solicitud_det 
						WHERE flag='1'
						GROUP BY codsolicitud) AS agricultor
					ON s.codsolicitud=agricultor.codsolicitud	LEFT JOIN
					(SELECT codsolicitud, SUM(IFNULL(kgxempaque,0)) AS cantidad 
						FROM lst_solicitud_com 
						WHERE flag='1'
						GROUP BY codsolicitud) AS compra
					ON s.codsolicitud=compra.codsolicitud LEFT JOIN
					(SELECT codsolicitud, SUM(IFNULL(kgxempaque*cantidad,0)) AS cantidad 
					FROM lst_proveedorventa 
					WHERE flag='1'
					GROUP BY codsolicitud) AS proventa
				 ON s.codsolicitud=proventa.codsolicitud
				 
				WHERE s.flag='1'  and s.numero is not null   $searchQuery" ;
			
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_solicitud($codsolicitud){
		
		$sql="SELECT * from lst_solicitud   where codsolicitud=$codsolicitud ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	
    public function estado_solicitud($codsolicitud,$estado,$usuario){
        $sql="update lst_solicitud 
				set estado='$estado', usuario_modifica='$usuario', fecha_modifica=now() 
				where codsolicitud=$codsolicitud";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	


	
	// COMPRA A TERCEROS
	//*******************************************


	// total de registros por auditor fecha
	public function selec_total_solicitud_compra($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
				FROM lst_proveedorcompra s
				WHERE s.flag='1'  $searchQuery " ;
				
				$sql="SELECT  COUNT(s.codtransaccion) AS total 
				FROM lst_proveedorcompra s inner join 
					prg_proyecto p on s.project_id=p.project_id 
					LEFT JOIN(
						SELECT GROUP_CONCAT(CONCAT_WS('=>',tiponormativa, STATUS) SEPARATOR '<br>') AS dato, codtransaccion 
						FROM lst_proveedorcompra_prog pp INNER JOIN lst_tiponormativa ON pp.codprograma=lst_tiponormativa.codtiponormativa
						WHERE pp.flag='1'
						GROUP BY codtransaccion 
						)AS vi ON s.codtransaccion=vi.codtransaccion
					INNER JOIN lst_cultivo c ON s.codcultivo=c.codcultivo	
					INNER JOIN lst_variedad v ON s.codvariedad=v.codvariedad	
				WHERE s.flag='1'and s.estado in ('a','d','p')   $searchQuery ";
				
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function select_solicitud_compra($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		
		$sql="SELECT distinct s.* , DATE_FORMAT(s.fecha,'%d/%m/%Y') AS fechaf, to_days(s.fecha) as dias,
					DATE_FORMAT(s.fechatc,'%d/%m/%Y') AS fechatcf,
					IFNULL(s.kgxempaque*s.cantidad,0) AS total,
					CASE estado WHEN 'a' THEN 'Aprobado' WHEN 'd' THEN 'No aprobado' ELSE 'Pendiente' END AS dscestado,
					p.proyect as proyecto,
					IFNULL(vi.dato,'') AS programadsc,
					c.cultivo AS cultivodsc,
					v.variedad AS variedaddsc,
					COUNT(ve.codventa) AS total2
				FROM lst_proveedorcompra s inner join 
					prg_proyecto p on s.project_id=p.project_id 
					LEFT JOIN(
						SELECT GROUP_CONCAT(CONCAT_WS('=>',tiponormativa, STATUS) SEPARATOR '<br>') AS dato, codtransaccion 
						FROM lst_proveedorcompra_prog pp INNER JOIN lst_tiponormativa ON pp.codprograma=lst_tiponormativa.codtiponormativa
						WHERE pp.flag='1'
						GROUP BY codtransaccion 
						)AS vi ON s.codtransaccion=vi.codtransaccion
					left JOIN lst_cultivo c ON s.codcultivo=c.codcultivo	
					left JOIN lst_variedad v ON s.codvariedad=v.codvariedad	
					LEFT JOIN lst_proveedorventa ve ON  ve.flag='1' AND ve.codtransaccion=s.codtransaccion
				WHERE s.flag='1'and s.estado in ('a','d','p')   $searchQuery 
				GROUP BY s.codtransaccion ";
    
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

    public function estado_solicitudcompra($codtransaccion,$estado){
        $sql="update lst_proveedorcompra set estado='$estado' where codtransaccion=$codtransaccion";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	
	// total de registros por auditor fecha
	public function selec_one_compra($codtransaccion){
		$sql="SELECT   c.*, DATE_FORMAT(c.fecha,'%d/%m/%Y') AS fechaf,
					SUM(c.cantidad*c.kgxempaque) AS ventaprov,
					DATE_FORMAT(c.fechatc,'%d/%m/%Y') AS fechatcf,
					CASE estado 
						WHEN 'p' THEN 'PENDIENTE'
						WHEN 'e' THEN 'ENVIADO'
						WHEN 'a' THEN 'APROBADO'
						ELSE ''
					END AS dscestado,
					t.tipoempaque,
					n.cultivo,
					s.variedad,
					vista_cer.norma,
					lst_certificadora.certificadora,
					t_mae_pais.nombre AS pais,
					IFNULL(vi.dato,'') AS programadsc
				FROM lst_proveedorcompra c  
					LEFT JOIN lst_tipoempaque t ON c.codtipoempaque=t.codtipoempaque
					LEFT JOIN lst_cultivo n ON c.codcultivo=n.codcultivo
					LEFT JOIN lst_variedad s ON c.codvariedad=s.codvariedad
					LEFT JOIN lst_certificadora ON c.codcertificadora=lst_certificadora.codcertificadora
					LEFT JOIN t_mae_pais ON c.codpais=t_mae_pais.id_pais
					LEFT JOIN(
						SELECT GROUP_CONCAT(CONCAT_WS('=>',tiponormativa, STATUS) SEPARATOR '<br>') AS dato, codtransaccion 
						FROM lst_proveedorcompra_prog pp INNER JOIN lst_tiponormativa ON pp.codprograma=lst_tiponormativa.codtiponormativa
						WHERE pp.flag='1'
						GROUP BY codtransaccion 
						)AS vi ON c.codtransaccion=vi.codtransaccion
					
					LEFT JOIN (
						SELECT GROUP_CONCAT(tiponormativa) AS norma, codtiponormativa
						FROM lst_tiponormativa
						WHERE flag='1' 
						GROUP BY codtiponormativa
					)  AS vista_cer ON c.codprograma=vista_cer.codtiponormativa	
					
				WHERE c.codtransaccion=$codtransaccion
				GROUP BY c.codtransaccion " ;
				
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	
	public function selec_oneid_paises($codpostal){
		$sql="SELECT * FROM t_mae_pais WHERE codpostal='$codpostal'";
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	// copia
	
	public function get_one_solicitud($codsolicitud){
        $sql="SELECT c.*,DATE_FORMAT(c.fecha,'%d/%m/%Y') AS fecha_f , 
				DATE_FORMAT(c.fecha_factura,'%d/%m/%Y') AS fecha_factura_f , 
				d.certificadora,
				t.tipocertificado,
				n.tiponormativa,
				s.tiposervicio,
				lst_semanas.nombre as semanadsc,
				DATE_FORMAT(lst_semanas.fecha_inicio,'%d/%m/%Y') semana_ini,
				DATE_FORMAT(lst_semanas.fecha_fin,'%d/%m/%Y') semana_fin,
				cu.cultivo as cultivodsc
			FROM lst_solicitud c 
					LEFT JOIN lst_certificadora d ON c.codcertificadora=d.codcertificadora 
					left join lst_tipocertificado t on c.codtipocertificado=t.codtipocertificado
					left join lst_tiponormativa n on c.codtiponormativa=n.codtiponormativa
					LEFT JOIN lst_tiposervicio s ON c.codtiposervicio=s.codtiposervicio
					inner join lst_semanas on c.semana=lst_semanas.id_semana
					inner join lst_cultivo cu on c.codcultivo=cu.codcultivo
			WHERE codsolicitud=$codsolicitud"; 
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	
	public function select_one_tiponormativa_ad($codtiponormativaad){
        $sql="select group_concat(distinct tiponormativaad) as tiponormativaad 
			from lst_tiponormativa_ad where codtiponormativaad in ($codtiponormativaad)";
         $consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function select_detlistaint_solrend($codlista,$codsolicitud,$tipo='left'){
		unset($this->listas);
		$sql="SELECT DISTINCT  d.*,s.empaque, s.kgxempaque, s.codtipoempaque,
				IFNULL(vista_lis.total,0) AS pedido, 
				(ifnull(rdtoalta,0) + ifnull(rdtobaja,0)) as rdtofin,
				(ifnull(rdtoalta,0) + ifnull(rdtobaja,0)) -  IFNULL(vista_lis.total,0) - IFNULL(vista_com.total,0)  AS restante,
				(ifnull(rdtoalta,0) + ifnull(stock,0)) -  IFNULL(vista_lis.total,0) - IFNULL(vista_com.total,0)  AS restante2,
				p.tipoempaque,
				c.kgxempaque AS cantidadnotc
			FROM lst_listaintegrada_det d left JOIN 
				lst_solicitud_det s ON d.coddetalle=s.coddetalle and s.codsolicitud=$codsolicitud LEFT JOIN
				lst_solicitud_com c ON d.coddetalle=c.coddetalle and c.codsolicitud=$codsolicitud left join
				lst_tipoempaque p on s.codtipoempaque=p.codtipoempaque 
				
				LEFT JOIN (
					SELECT SUM(empaque*kgxempaque) AS total, coddetalle
					FROM lst_solicitud_det de INNER JOIN lst_solicitud se ON se.codsolicitud = de.codsolicitud
					WHERE se.flag='1' AND de.flag='1' AND se.estado in ('a','e') and se.codsolicitud!=$codsolicitud AND  (se.codsolicitud<$codsolicitud OR numero_mostrar='INICIAL')
					GROUP BY de.coddetalle
				) AS vista_lis ON d.coddetalle=vista_lis.coddetalle
				LEFT JOIN (
					SELECT SUM(kgxempaque) AS total, coddetalle, DATE_FORMAT(MAX(so.fecha),'%d/%m/%Y') AS fecha
					FROM lst_solicitud_com  co INNER JOIN lst_solicitud so ON so.codsolicitud = co.codsolicitud
					WHERE so.flag='1' AND co.flag='1' AND so.estado in ('a','e') and so.codsolicitud!=$codsolicitud AND  (so.codsolicitud<$codsolicitud OR numero_mostrar='INICIAL')
					GROUP BY co.coddetalle
				)  AS vista_com ON d.coddetalle=vista_com.coddetalle
				
			WHERE d.codlista=$codlista ";
		if($tipo=='inner')
				$sql.=" AND  s.empaque *s.kgxempaque>0";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_detlistacompras($project_id,$codsolicitud,$tipo='left'){
		unset($this->listas);
		$sql="SELECT distinct p.* , DATE_FORMAT(p.fechatc,'%d/%m/%Y') AS fechatcf,
				IFNULL(v.codtipoempaque,'') codtipoempaquev, IFNULL(v.kgxempaque,'') kgxempaquev, 
				p.kgxempaque*p.cantidad  AS comprado,
				p.kgxempaque*p.cantidad  - IFNULL(vista.total,0)  AS disponible,
				p.kgxempaque*p.cantidad - IFNULL(vista.total,0) - IFNULL(v.kgxempaque*v.cantidad,0) AS restante,
				IFNULL(v.cantidad,'')  cantidadv,
				IFNULL(v.cantidad,0)*IFNULL(v.kgxempaque,0) as totalv,
				IFNULL(vista.total,0) AS vendido,
				d.certificadora,
				c.cultivo,
				q.tipoempaque,
				va.variedad
			FROM lst_proveedorcompra p 
					$tipo JOIN lst_proveedorventa v ON p.codtransaccion=v.codtransaccion AND v.codsolicitud=$codsolicitud
					LEFT JOIN lst_cultivo c ON p.codcultivo=c.codcultivo
					LEFT JOIN lst_variedad va ON p.codvariedad=va.codvariedad
					LEFT JOIN lst_certificadora d ON p.codcertificadora=d.codcertificadora
					left join lst_tipoempaque q on v.codtipoempaque=q.codtipoempaque
				LEFT JOIN (
					SELECT SUM(pv.kgxempaque*pv.cantidad) AS total, 
					pv.codtransaccion
					FROM lst_proveedorventa pv INNER JOIN lst_solicitud so ON pv.codsolicitud=so.codsolicitud
					WHERE pv.flag='1' AND so.flag='1' AND so.estado in ('e','a') and pv.codsolicitud!=$codsolicitud
					GROUP BY pv.codtransaccion
				) AS vista ON p.codtransaccion=vista.codtransaccion
			WHERE p.flag='1' and p.estado in ('e','a') AND p.project_id='$project_id' ";
			
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_tipoempaque($id_pais){
		unset($this->lista);
		$this->lista=[];
        $sql="select * from lst_tipoempaque where flag='1' and id_pais='$id_pais' order by tipoempaque";
        $consulta=$this->db->consultar($sql);
		if(!empty($consulta)){
			foreach($consulta as $filas){
			   $this->lista[]=$filas;
			}
		}
        return $this->lista;
	}	
	
	public function select_resumen_listaint($codsolicitud,$codlista){
		unset($this->listas);
		$sql="SELECT SUM(total) AS total, SUM(empaque) AS empaque, kgxempaque, tipoempaque FROM 
			( 	SELECT 	empaque, kgxempaque, empaque*kgxempaque AS total,  tipoempaque
				FROM lst_solicitud_det s INNER JOIN lst_tipoempaque t ON s.codtipoempaque=t.codtipoempaque
				WHERE codsolicitud=$codsolicitud and codlista=$codlista AND s.flag='1'
				
			) AS vista
			GROUP BY tipoempaque,kgxempaque  ";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
		
        return $this->listas;	
	}	
	
	public function select_resumen_proventa($codsolicitud){
		unset($this->listas);
		$sql="SELECT 	SUM(cantidad) AS cantidad, kgxempaque, SUM(cantidad*kgxempaque) AS total,  tipoempaque
				FROM lst_proveedorventa s INNER JOIN lst_tipoempaque t ON s.codtipoempaque=t.codtipoempaque
				WHERE codsolicitud=$codsolicitud AND s.flag='1'
				GROUP BY kgxempaque,tipoempaque";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function get_one_proyecto($project_id){
        $sql="select distinct p.*,cp.categoria
			from lst_proyecto p
				LEFT JOIN
				prg_proyecto_categoria pc ON (p.project_id=pc.project_id)
				LEFT JOIN
				prg_categoria_proy cp ON (cp.codcategoria=pc.codcategoria)

			 where p.project_id='$project_id' ";
		if(!empty($id_pais)) $sql.=" and p.id_pais='$id_pais' and pc.id_pais ='$id_pais'";
		$sql.=" order by p.proyecto"; 
		
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
    }
	
	public function get_one_lista_cultivo($codlista){
        $sql="SELECT GROUP_CONCAT(DISTINCT cultivo) AS cultivo, GROUP_CONCAT(DISTINCT variedad) AS variedad
				FROM lst_listaintegrada_det 
				WHERE codlista=$codlista"; 
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	
	public function get_one_listabyId($codlista){
        $sql="select *, DATE_FORMAT(fechainicio,'%d/%m/%Y') AS fechainiciof,
				IFNULL(DATE_FORMAT(fechatermino,'%d/%m/%Y'),'') AS fechaterminof, ifnull(rutafile,'') as rutaarchivo 
			from lst_listaintegrada where codlista=$codlista "; 
			
     		$consulta=$this->db->consultarOne($sql);
        return $consulta;
    }
	
	public function get_filebyLista($codlista){
		
        $sql="select distinct rutafile, DATE_FORMAT(fecha,'%d/%m/%Y') AS fechaf
				from lst_listaintegrada_file where codlista=$codlista "; 
				
        $consulta=$this->db->consultar($sql);
     
		if(!empty($consulta)){
			foreach($consulta as $filasf){
				$this->listasf[]=$filasf;
			}
		
        return $this->listasf;
		}else return "";
    }
	
	public function get_one_solicitud_detalle($codlista){
        $sql="SELECT 
					codproyecto,proyecto, DATE_FORMAT(fechainicio,'%d/%m/%Y') AS fechainicio,
					DATE_FORMAT(fechatermino,'%d/%m/%Y') AS fechatermino, 
					IFNULL(tole.tolerancia,'') AS tolerancia,
					IFNULL(vista.categoria,'') AS categoria,
					COUNT(DISTINCT cedula) AS productores,
					COUNT(DISTINCT codunidad) AS unidades,
					COUNT(DISTINCT codcampo) AS campos,
					l.tipo
				FROM lst_listaintegrada l LEFT JOIN 
					(SELECT GROUP_CONCAT(DISTINCT categoria) AS categoria, project_id,prg_proyecto_categoria.id_pais
					 FROM prg_proyecto_categoria INNER JOIN prg_categoria_proy ON  prg_proyecto_categoria.codcategoria = prg_categoria_proy.codcategoria 
					GROUP BY project_id, id_pais) AS vista ON l.codproyecto=vista.project_id AND l.id_pais=vista.id_pais
					INNER JOIN lst_listaintegrada_det d ON l.codlista=d.codlista
					LEFT JOIN (
						SELECT GROUP_CONCAT(CONCAT(cultivo,' ',tolerancia,' %')) AS tolerancia, codlista
						FROM lst_listadetxtolerancia tol INNER JOIN lst_cultivo c ON tol.codcultivo=c.codcultivo  
						WHERE tol.flag='1'
						GROUP BY codlista
					) AS tole ON l.codlista=tole.codlista
				WHERE l.codlista=$codlista
				GROUP BY l.codlista"; 

		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	
	public function selec_paises(){
		unset($this->listas);
		$sql="SELECT id_pais, nombre from t_mae_pais where flag='1' and ifnull(codpostal,'')!='' order by nombre";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
}
?>