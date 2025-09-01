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
	
	public function selec_tipodsc($tipo=null,$flgcel=null){
		unset($this->listas);
		$sql="SELECT codtipo, tipodsc 
			FROM inv_tipo 
			WHERE flag='1' ";
		if(!empty($tipo))
				$sql.=" and categoria='$tipo' ";
			
		if(!empty($flgcel))
				$sql.=" and flgcel='$flgcel' ";
			
		$sql.="	order by tipodsc";
	
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function selec_marca($flgcelular=0, $flgit=0){
		unset($this->listas);
		$sql="SELECT codmarca, marca FROM inv_marca WHERE flag='1' ";
		if(!empty($flgcelular))
			$sql.=" and flgcelular='$flgcelular' ";
		if(!empty($flgit))
			$sql.=" and flgit='$flgit' ";
		
		$sql.=" order by marca";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	

	public function select_producto_noselect($id_pais,$tipo=null,$all=null){
		unset($this->listas);
		$sql="SELECT inv_producto.*, 
				case when inv_producto.codtipo <=4 then
					concat_ws(' ',producto,marca,inv_producto.modelo,inv_producto.serief)
				else
					concat_ws(' ',producto,inv_producto.modelo)
				end	as detalle,
				IFNULL(inv_producto.descripcion,'') AS descripcion,
				IFNULL(inv_producto.modelo,'') AS modelo,
				IFNULL(inv_producto.serief,'') AS serief,
				marca, tipodsc,
				date_format(fechacompra,'%d/%m/%Y') as fechacompraf
			FROM inv_producto LEFT JOIN inv_marca ON  inv_producto.codmarca=inv_marca.codmarca
				INNER JOIN inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo
			WHERE inv_producto.id_pais='$id_pais' and inv_producto.flag='1' and activo='1'
				AND codproducto NOT IN (SELECT DISTINCT codproducto 
										FROM inv_dettransaccion d INNER JOIN inv_transaccion t ON d.codtransaccion=t.codtransaccion
											INNER JOIN inv_motivo m ON t.codmotivo=m.codmotivo
										WHERE d.flag='1' AND  t.flag='1' ";
											
				if(empty($all))	$sql.="	AND  ((d.flgactivo='1' AND m.tipo='x') or tipo='s') )";
				else			$sql.=" and tipo='s' )"; 
									
		if(!empty($tipo))
			$sql.=" and inv_tipo.categoria='$tipo' ";
		
		
		$sql.=" order by producto " ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}

		
	public function selec_one_tipo($codtipo){
		$sql=" SELECT * from inv_tipo where codtipo=$codtipo" ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_auditor($id_auditor){
		$sql="SELECT * fROM prg_auditor WHERE flag='1' AND id_auditor=$id_auditor";
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_total_producto($searchQuery=null){
		$sql=" SELECT COUNT(distinct inv_producto.codproducto) AS total 
				FROM inv_producto left JOIN 
					inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo
					LEFT JOIN 
						( SELECT MAX(coddetalle), usuariodestino,codproducto,codarea ,inv_motivo.motivo, inv_motivo.tipo
						FROM inv_dettransaccion ft INNER JOIN inv_transaccion ON ft.codtransaccion=inv_transaccion.codtransaccion
							INNER JOIN inv_motivo ON inv_transaccion.codmotivo=inv_motivo.codmotivo
						WHERE ft.flag='1' AND ft.flgactivo='1'  AND inv_motivo.tipo IN ('x','s') AND inv_motivo.flag='1'
						GROUP BY codproducto) AS vista ON inv_producto.codproducto=vista.codproducto
				WHERE inv_producto.flag='1' $searchQuery " ;
				
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}


	public function select_producto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas); // numero
		$sql="SELECT distinct inv_producto.*, 
				case when ifnull(cargador,'')='SI' then 'SI' else 'NO' end as cargadordsc,
				case when ifnull(simcard,'')='SI' then 'SI' else 'NO' end as simcarddsc,
				ifnull(inv_producto.descripcion,'') as descripcion,
				ifnull(inv_producto.modelo,'') as modelo,
				ifnull(inv_producto.serief,'') as serief,
				concat_ws(' ',producto,marca,modelo,serief) as parabarra,
				marca, tipodsc,
				IFNULL(vista.usuariodestino,'') AS usuariodestino,
				ifnull(inv_area.area,'') as area,
				ifnull(inv_sede.sede,'') as sede,
				date_format(fechacompra,'%d/%m/%Y') as fechacompraf,
				to_days(fechacompra) as dias,
				CASE WHEN IFNULL(vista.motivo,'')='' THEN 'NO ASIGNADO'
					ELSE vista.motivo END AS estado,
				vista.fechatra, vista.descripciontra, vista.ubicacion,descripciondet,
				inv_producto.estado as estadodsc,area,sede
				FROM inv_producto left JOIN 
					inv_marca ON  inv_producto.codmarca=inv_marca.codmarca left JOIN 
					inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo
					LEFT JOIN 
						( SELECT MAX(coddetalle), usuariodestino,
							codproducto,codarea ,codsede,
							inv_motivo.motivo, 
							inv_motivo.tipo,
							date_format(inv_transaccion.fecha,'%d/%m/%Y') as fechatra,
							inv_transaccion.descripcion as descripciontra,
							ft.descripcion as descripciondet,
							inv_transaccion.ubicacion
						FROM inv_dettransaccion ft INNER JOIN 
							 inv_transaccion ON ft.codtransaccion=inv_transaccion.codtransaccion INNER JOIN 
							 inv_motivo ON inv_transaccion.codmotivo=inv_motivo.codmotivo
						WHERE ft.flag='1' AND ft.flgactivo='1'  AND inv_motivo.tipo IN ('x','s') AND inv_motivo.flag='1'
						GROUP BY codproducto) AS vista ON inv_producto.codproducto=vista.codproducto
					left join inv_area on vista.codarea=inv_area.codarea
					left join inv_sede on vista.codsede=inv_sede.codsede
					
				WHERE inv_producto.flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function select_producto_byadmin($id_pais){
		unset($this->listas);
		$sql="SELECT DISTINCT CONCAT_WS('::',codigo,producto) AS producto,codproducto , umedida
			FROM inv_producto LEFT JOIN 
				inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo
			WHERE inv_producto.flag='1'  AND categoria='a' and activo='1'
			ORDER BY producto" ;
				
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	// total de registros por auditor fecha
	
	
	
	public function buscarserie_producto($serief){
		$sql=" SELECT *
				FROM inv_producto 
				WHERE flag='1' and serief='$serief'
				AND codproducto NOT IN (
				SELECT  codproducto FROM inv_transaccion t INNER JOIN inv_dettransaccion d ON t.codtransaccion=d.codtransaccion
				INNER JOIN inv_motivo ON t.codmotivo=inv_motivo.codmotivo AND tipo='s'
				WHERE t.flag='1' AND d.flag='1' )				" ;
			
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
		
		$sql="SELECT inv_producto.*, marca, tipodsc, 1 as cantidad,
				date_format(fechainicio,'%d/%m/%Y') as fechainiciof,
				DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 365 DAY),'%d/%m/%Y')  AS proximo,
				DATE_FORMAT(DATE_ADD(now(), INTERVAL 365 DAY),'%d/%m/%Y')  AS fechainiciofprox,
				date_format(inigarantia,'%d/%m/%Y') as inigarantiaf,
				date_format(fingarantia,'%d/%m/%Y') as fingarantiaf,
				date_format(fechacompra,'%d/%m/%Y') as fechacompraf,
				
				case cargador when 'SI' then 'SI' else 'NO' end as cargadordsc,
				case simcard when 'SI' then 'SI' else 'NO' end as simcarddsc,
				vista.usuariodestino,
				vista.fechatra
				
				FROM inv_producto LEFT JOIN inv_marca ON  inv_producto.codmarca=inv_marca.codmarca
					INNER JOIN inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo
					
					LEFT JOIN 
						( SELECT MAX(coddetalle), usuariodestino,
							codproducto,codarea ,codsede,
							inv_motivo.motivo, 
							inv_motivo.tipo,
							date_format(inv_transaccion.fecha,'%d/%m/%Y') as fechatra,
							inv_transaccion.descripcion as descripciontra,
							ft.descripcion as descripciondet,
							inv_transaccion.ubicacion
						FROM inv_dettransaccion ft INNER JOIN 
							 inv_transaccion ON ft.codtransaccion=inv_transaccion.codtransaccion INNER JOIN 
							 inv_motivo ON inv_transaccion.codmotivo=inv_motivo.codmotivo
						WHERE ft.flag='1' AND ft.flgactivo='1'  AND inv_motivo.tipo IN ('x','s') AND inv_motivo.flag='1'
						GROUP BY codproducto) AS vista ON inv_producto.codproducto=vista.codproducto
						
				where inv_producto.codproducto=$codproducto ";
			
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_stock($codproducto){
		
		$sql="SELECT 
				SUM(CASE tipo 
						WHEN 'x' THEN cantidad*-1 
						WHEN 's' THEN cantidad*-1 WHEN 'i' THEN cantidad ELSE 0 END) AS stock
				
			FROM inv_dettransaccion d  inner JOIN 
					inv_transaccion tr ON d.codtransaccion=tr.codtransaccion inner JOIN
				inv_motivo ON tr.codmotivo=inv_motivo.codmotivo
					
			WHERE  d.flag='1' AND tr.flag='1' and codproducto=$codproducto ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
	}
	
	public function selec_all_producto($codtransaccion){
		
		$sql="SELECT inv_producto.*, marca, tipodsc, 1 as cantidad,
				date_format(fechainicio,'%d/%m/%Y') as fechainiciof,
				date_format(inigarantia,'%d/%m/%Y') as inigarantiaf,
				date_format(fingarantia,'%d/%m/%Y') as fingarantiaf,
				date_format(fechacompra,'%d/%m/%Y') as fechacompraf,
				case cargador when 'SI' then 'SI' else 'NO' end as cargadordsc,
				case simcard when 'SI' then 'SI' else 'NO' end as simcarddsc
				
				FROM inv_producto LEFT JOIN inv_marca ON  inv_producto.codmarca=inv_marca.codmarca
					INNER JOIN inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo
					inner join inv_dettransaccion on inv_producto.codproducto=inv_dettransaccion.codproducto
				where inv_dettransaccion.flag='1' and codtransaccion = $codtransaccion ";
				
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	 public function insert_producto($numero,$codempresa,$codigo,$cargador,$simcard,$estado,$imei,$stock_min,$producto,$onedrive,$antivirus,$clase,$so,$office,$dominio,$tamanio,$codmarca,$codtipo,$modelo,$serief,$ram,$procesador,$host,$hd1,$fechainicio,$periodo,$proveedor,$fechacompra,$nrodocumento,$moneda,$costo,$inigarantia,$fingarantia,$id_pais,$usuario,$ip){

		$fechainicio = !empty($fechainicio) ? "'$fechainicio'" : "NULL";
		$fechacompra = !empty($fechacompra) ? "'$fechacompra'" : "NULL";
		$inigarantia = !empty($inigarantia) ? "'$inigarantia'" : "NULL";
		$fingarantia = !empty($fingarantia) ? "'$fingarantia'" : "NULL";
		$codmarca = !empty($codmarca) ? "$codmarca" : "NULL";
		$costo = !empty($costo) ? "$costo" : "NULL";
		$stock_min = !empty($stock_min) ? "$stock_min" : "NULL";
		$periodo = !empty($periodo) ? "$periodo" : "NULL";
		$codempresa = !empty($codempresa) ? "$codempresa" : "NULL";
 
        $sql="insert into inv_producto(numero,codempresa,codigo,cargador,simcard,estado,imei,stock_min,producto,onedrive,antivirus,clase,fechainicio,periodo,serief,ram,procesador,host,hd1,so,office,dominio,tamanio,codmarca,codtipo,modelo,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica,proveedor,fechacompra,nrodocumento,moneda,costo,inigarantia,fingarantia)
        values('$numero',$codempresa,'$codigo','$cargador','$simcard','$estado','$imei',$stock_min,'$producto','$onedrive','$antivirus','$clase',$fechainicio,$periodo,'$serief','$ram','$procesador','$host','$hd1','$so','$office','$dominio','$tamanio',$codmarca,$codtipo,'$modelo','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip','$proveedor',$fechacompra,'$nrodocumento','$moneda',$costo,$inigarantia,$fingarantia)";
		
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_producto($numero,$codempresa,$codigo,$cargador,$simcard,$estado,$imei,$stock_min,$producto,$onedrive,$antivirus,$clase,$codproducto,$so,$office,$dominio,$tamanio,$codmarca,$codtipo,$modelo,$serief,$ram,$procesador,$host,$hd1,$fechainicio,$periodo,$proveedor,$fechacompra,$nrodocumento,$moneda,$costo,$inigarantia,$fingarantia,$usuario,$ip){
		$costo = !empty($costo) ? "$costo" : "NULL";
		$fechainicio = !empty($fechainicio) ? "'$fechainicio'" : "NULL";
		$fechacompra = !empty($fechacompra) ? "'$fechacompra'" : "NULL";
		$inigarantia = !empty($inigarantia) ? "'$inigarantia'" : "NULL";
		$fingarantia = !empty($fingarantia) ? "'$fingarantia'" : "NULL";
		$codempresa = !empty($codempresa) ? "$codempresa" : "NULL";
		
        $sql="update inv_producto 
				set 
					codempresa=$codempresa,
					numero='$numero',
					codigo='$codigo',
					cargador='$cargador',
					simcard='$simcard',
					estado='$estado',
					imei='$imei',
					stock_min='$stock_min',
					producto='$producto',
					onedrive='$onedrive',
					antivirus='$antivirus',
					clase='$clase',
					so='$so',
					office='$office',
					dominio='$dominio',
					tamanio='$tamanio',
					codmarca='$codmarca',
					serief='$serief',
					codtipo='$codtipo',
					modelo='$modelo',
					ram='$ram',
					procesador='$procesador',
					host='$host',
					hd1='$hd1',
					fechainicio=$fechainicio,
					periodo='$periodo',
					proveedor='$proveedor',
					fechacompra=$fechacompra,
					nrodocumento='$nrodocumento',
					moneda='$moneda',
					costo=$costo,
					inigarantia=$inigarantia,
					fingarantia=$fingarantia,
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


	
	//*******************************************************
	// movimientos
	//*******************************************************
	public function select_movimento($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas); // to_days
		$sql="
			SELECT imei,
				CONCAT_WS('-',LPAD(t.codtransaccion,4,'0'),YEAR(t.fecha)) AS numero,
				CONCAT_WS('_',d.coddetalle,t.codtransaccion,d.codproducto,inv_tipo.categoria) AS llave,
				inv_producto.producto, inv_producto.codproducto,modelo, inv_producto.numero as numerotel,
				ifnull(procesador,'') as procesador,ram, hd1,
				marca, tipodsc, serie,serief,
				t.codtransaccion,d.coddetalle,
				ifnull(t.archivo,'') as archivo,
				fullusuario AS usuariodestino,
				inv_usuario.codusuario,
				case cargador when 'SI' then 'SI' else 'NO' end as cargadordsc,
				case simcard when 'SI' then 'SI' else 'NO' end as simcarddsc,
				d.cantidad,d.so,
				d.agencia, 
				d.precio, 
				d.moneda,d.numtelefono,
				DATE_FORMAT(t.fecha,'%d/%m/%Y') AS fechaf,
				to_days(t.fecha) as dias,
				IFNULL(DATE_FORMAT(d.fecharetiro,'%d/%m/%Y'),'') AS fecharetirof,
				d.office,d.dominio,d.antivirus,
				flgactivo,
				IFNULL(inv_area.area,'') AS area,
				ifnull(t.ubicacion,'') as ubicacion,
				codigo,umedida,sede
				FROM inv_producto LEFT JOIN inv_marca ON  inv_producto.codmarca=inv_marca.codmarca
					LEFT JOIN inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo
					INNER JOIN inv_dettransaccion d ON inv_producto.codproducto=d.codproducto 
					INNER JOIN inv_transaccion t ON d.codtransaccion=t.codtransaccion 
					LEFT JOIN inv_usuario ON t.coddestino=inv_usuario.codusuario
					LEFT JOIN inv_area ON inv_usuario.codarea=inv_area.codarea
					inner join inv_motivo on t.codmotivo=inv_motivo.codmotivo 
					left join inv_sede on t.codsede=inv_sede.codsede
				WHERE inv_producto.flag='1' and activo='1' AND t.flag='1' and d.flag='1'  $searchQuery ";
				
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_movimento($searchQuery=null){
		$sql=" 
			SELECT count(d.coddetalle) AS total
			FROM inv_producto LEFT JOIN inv_marca ON  inv_producto.codmarca=inv_marca.codmarca
				LEFT JOIN inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo
				INNER JOIN inv_dettransaccion d ON inv_producto.codproducto=d.codproducto AND flgactivo='1'
				INNER JOIN inv_transaccion t ON d.codtransaccion=t.codtransaccion 
				LEFT JOIN inv_usuario ON t.coddestino=inv_usuario.codusuario
				inner join inv_motivo on t.codmotivo=inv_motivo.codmotivo
			WHERE inv_producto.flag='1'  and activo='1' AND t.flag='1' and d.flag='1' $searchQuery " ;
			
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	// devolucion
	//*******************************************************

	public function select_one_devolucion($coddevolucion){
		unset($this->listas);
		$sql="
			SELECT d.*, u.fullusuario, ifnull(u.dni,'') as dni,
				CONCAT_WS('-',LPAD(d.coddevolucion,4,'0'),YEAR(d.fecha)) AS numero,
				DATE_FORMAT(d.fecha,'%d/%m/%Y') AS fechaf
				FROM inv_devolucion d inner JOIN 
					inv_usuario  u ON d.codusuario=u.codusuario
				WHERE d.coddevolucion=$coddevolucion ";
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
		
	}

	public function select_devolucion($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="
			SELECT d.*, u.fullusuario,
				CONCAT_WS('-',LPAD(d.coddevolucion,4,'0'),YEAR(d.fecha)) AS numero,
				DATE_FORMAT(d.fecha,'%d/%m/%Y') AS fechaf
				FROM inv_devolucion d inner JOIN 
					inv_usuario  u ON d.codusuario=u.codusuario
				WHERE d.flag='1'  AND u.flag='1'  $searchQuery ";
				
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_total_devolucion($searchQuery=null){
		$sql=" 
			SELECT count(d.coddevolucion) AS total
			FROM inv_devolucion d inner JOIN 
					inv_usuario  u ON d.codusuario=u.codusuario
				WHERE d.flag='1'  AND u.flag='1'  $searchQuery " ;
			
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	// fin devolucion
	
	public function selec_one_transacccion($codtransaccion){
		
		$sql="SELECT inv_transaccion.*,inv_usuario.fullusuario, ifnull(inv_usuario.dni,'') as dni,
					ifnull(inv_usuario.email,'') as email,
					CONCAT_WS('-',LPAD(codtransaccion,4,'0'),YEAR(fecha)) AS numero,
					DATE_FORMAT(fecha,'%d/%m/%Y') AS fechaf,
					area, motivo
					
				FROM inv_transaccion left join inv_usuario on inv_transaccion.coddestino=inv_usuario.codusuario  LEFT JOIN
					inv_motivo ON inv_transaccion.codmotivo=inv_motivo.codmotivo LEFT JOIN
					inv_area ON inv_transaccion.codarea=inv_area.codarea 
					
				where codtransaccion=$codtransaccion ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_one_dettransacccion($coddetalle){
		
		$sql="SELECT *,DATE_FORMAT(fecha,'%d/%m/%Y') AS fechaf, 
				DATE_FORMAT(fecharetiro,'%d/%m/%Y') AS fecharetirof 
				FROM inv_dettransaccion where coddetalle=$coddetalle ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_one_empresabytransac($codtransaccion){
		
		$sql="SELECT DISTINCT empresa FROM inv_empresa INNER JOIN 
					inv_producto ON inv_empresa.codempresa=inv_producto.codempresa INNER JOIN
					inv_dettransaccion ON inv_producto.codproducto=inv_dettransaccion.codproducto
				WHERE codtransaccion=	$codtransaccion ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_all_dettransacccion($codtransaccion){
		unset($this->listas);
		$sql="SELECT p.*,DATE_FORMAT(d.fecha,'%d/%m/%Y') AS fechaf, 
				DATE_FORMAT(fecharetiro,'%d/%m/%Y') AS fecharetirof , marca, tipodsc,flgdato,
				 p.codtipo
				FROM inv_dettransaccion d 
					INNER JOIN inv_producto p ON d.codproducto=p.codproducto
					INNER JOIN inv_marca m ON p.codmarca=m.codmarca
					INNER JOIN inv_tipo t ON p.codtipo=t.codtipo
				WHERE d.flag='1' AND p.flag='1' AND codtransaccion=$codtransaccion ";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_all_dettransacccion_bydevol($coddevolucion){
		unset($this->listas);
		$sql="SELECT p.*,DATE_FORMAT(d.fecha,'%d/%m/%Y') AS fechaf, 
				DATE_FORMAT(fecharetiro,'%d/%m/%Y') AS fecharetirof , marca, tipodsc,flgdato
				FROM inv_dettransaccion d INNER JOIN inv_producto p ON d.codproducto=p.codproducto
					INNER JOIN inv_marca m ON p.codmarca=m.codmarca
					INNER JOIN inv_tipo t ON p.codtipo=t.codtipo
				WHERE d.flag='1' AND p.flag='1' AND d.coddevolucion =$coddevolucion ";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_dettransacccion_bydet($coddetalle){
		
		$sql="SELECT *,DATE_FORMAT(fecha,'%d/%m/%Y') AS fechaf, 
				DATE_FORMAT(fecharetiro,'%d/%m/%Y') AS fecharetirof 
				FROM inv_dettransaccion where coddetalle in ($coddetalle) ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_one_dettransacccion_bycod($codtransaccion){
		
		$sql="SELECT *,DATE_FORMAT(fecha,'%d/%m/%Y') AS fechaf, 
				DATE_FORMAT(fecharetiro,'%d/%m/%Y') AS fecharetirof 
				FROM inv_dettransaccion where codtransaccion=$codtransaccion ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_transacc_producto($codproducto){
		$sql="SELECT inv_dettransaccion.*, usuariodestino, DATE_FORMAT(inv_dettransaccion.fecha,'%d/%m/%Y') AS fechaf	
					FROM inv_dettransaccion INNER JOIN inv_transaccion ON inv_dettransaccion.codtransaccion=inv_transaccion.codtransaccion
					WHERE codproducto=$codproducto AND flgactivo='1' AND inv_transaccion.flag='1'  AND inv_dettransaccion.flag='1'
					ORDER BY 1 DESC limit 0,1 ";
		$consulta=$this->db->consultarOne($sql);
		
        return $consulta;	
		
	}
	
	public function insert_transaccion($ubicacion,$codarea,$codsede,$codmotivo,$coddestino,$descripcion,$fecha,$id_pais,$usuario,$ip){


		$codarea = !empty($codarea) ? "'$codarea'" : "NULL";
		$codsede = !empty($codsede) ? "'$codsede'" : "NULL";
		$coddestino = !empty($coddestino) ? "'$coddestino'" : "NULL";

        $sql="insert into inv_transaccion(ubicacion,codarea,codsede,codmotivo,coddestino,descripcion,fecha,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$ubicacion',$codarea,$codsede,$codmotivo,$coddestino,'$descripcion','$fecha','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";

		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	
	
	public function insert_transaccion2($nrodocumento,$codempresa,$proveedor,$codarea,$codsede,$codmotivo,$coddestino,$descripcion,$fecha,$id_pais,$usuario,$ip){


		$codarea = !empty($codarea) ? "'$codarea'" : "NULL";
		$codsede = !empty($codsede) ? "'$codsede'" : "NULL";
		$coddestino = !empty($coddestino) ? "'$coddestino'" : "NULL";

        $sql="insert into inv_transaccion(comprobante,codempresa2,proveedor2,ubicacion,codarea,codsede,codmotivo,coddestino,descripcion,fecha,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$nrodocumento','$codempresa','$proveedor','$ubicacion',$codarea,$codsede,$codmotivo,$coddestino,'$descripcion','$fecha','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";

		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	
	
	public function update_transaccion($codtransaccion,$ubicacion,$codarea,$codsede,$codmotivo,$coddestino,$descripcion,$fecha,$usuario,$ip){
	   
        $sql="update inv_transaccion 
				set 
					ubicacion='$ubicacion',
					descripcion='$descripcion',
					coddestino='$coddestino',
					fecha='$fecha',
					codarea=$codarea,
					codsede=$codsede,
					codmotivo=$codmotivo,
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codtransaccion=$codtransaccion ";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function update_transaccion2($codtransaccion,$nrodocumento,$codempresa,$proveedor,$fecha,$codpais,$usuario,$ip){
	   
        $sql="update inv_transaccion 
				set 
					fecha='$fecha',
					comprobante='$nrodocumento',
					codempresa2='$codempresa',
					proveedor2='$proveedor',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codtransaccion=$codtransaccion ";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function update_fileTransaccion($codtransaccion,$archivo){
	   
        $sql="update inv_transaccion 
				set 
					archivo='$archivo'
                where codtransaccion in ($codtransaccion) ";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function update_fileTransaccion_dev($coddevolucion,$archivo){
	   
        $sql="update inv_devolucion
				set archivo='$archivo' 
				where coddevolucion =$coddevolucion";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	// elimina devolucion
	public function delete_devolucion($coddevolucion,$usuario,$ip){
	   
        $sql="update inv_devolucion
				set flag='0' 
				where coddevolucion =$coddevolucion";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function update_transaccion_anula_devolucion($coddevolucion,$usuario,$ip){
		 $sql="UPDATE inv_dettransaccion
				set fecharetiro=null,
					descripcion='',
					coddevolucion=null,
					flgactivo='1',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
				WHERE coddevolucion = $coddevolucion ";

		$consulta=$this->db->execute($sql);
        return $consulta;
	}
	
	
	public function insert_dettransaccion($cantidad,$codtransaccion,$codproducto,$descripcion,$fecha,$numtelefono,$agencia,$moneda,$precio,$usuario,$ip){
		$precio = !empty($precio) ? "$precio" : "NULL";		  
        $sql="insert ignore  into inv_dettransaccion(cantidad,codtransaccion,descripcion,fecha,codproducto,flgactivo,numtelefono,agencia,moneda,precio,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values($cantidad,$codtransaccion,'$descripcion','$fecha',$codproducto,'1','$numtelefono','$agencia','$moneda',$precio,'1','$usuario',now(),'$ip','$usuario',now(),'$ip')";

		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	
	
	public function update_dettransaccion($coddetalle,$cantidad=1,$codtransaccion,$codproducto,$descripcion,$fecha,$numtelefono,$agencia,$moneda,$precio,$usuario,$ip){
	   
        $sql="update inv_dettransaccion 
				set 
					descripcion='$descripcion',
					numtelefono='$numtelefono',
					agencia='$agencia',
					cantidad='$cantidad',
					moneda='$moneda',
					precio='$precio',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where coddetalle=$coddetalle and codtransaccion=$codtransaccion and codproducto=$codproducto";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function update_dettransaccion2($cantidad,$coddetalle,$codproducto,$fecha,$precio,$usuario,$ip){
	   
        $sql="update inv_dettransaccion 
				set 
					fecha='$fecha',
					cantidad='$cantidad',
					precio='$precio',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where coddetalle=$coddetalle and codproducto=$codproducto";
		
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

	public function update_transaccion_des($coddevolucion,$coddetalle,$descripcion,$fecharetiro,$usuario,$ip){
		 $sql="UPDATE inv_dettransaccion
				set fecharetiro='$fecharetiro',
					descripcion='$descripcion',
					coddevolucion=$coddevolucion,
					flgactivo='0',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
				WHERE coddetalle in ($coddetalle) ";

		$consulta=$this->db->execute($sql);
        return $consulta;
	}
	// devolucion
	
	public function insert_devolucion($descripcion,$fecha,$codusuario,$tipo,$usuario,$ip){
		
		$sql=" insert into inv_devolucion(codusuario,fecha,descripcion,tipo,usuario_ingreso,fecha_ingreso,ip_ingreso,flag)
			values ($codusuario,'$fecha','$descripcion','$tipo','$usuario',now(),'$ip','1')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
	}
	
	public function update_transaccion_des_byprod($codproducto,$usuario,$ip){
		 $sql="UPDATE inv_dettransaccion
				set fecharetiro=now(),
					descripcion='Salida del equipo',
					flgactivo='0',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
				WHERE codproducto in ($codproducto) and flgactivo='1' and codtransaccion in ( select codtransaccion from inv_transaccion where flag='1' and  codmotivo in (3,8) ) ";

		$consulta=$this->db->execute($sql);
        return $consulta;
	}
	
	public function delete_dettransaccion($codtransaccion,$coddetalle,$codproducto){
	   
        $sql="update inv_dettransaccion set flag='0' where coddetalle=$coddetalle and codproducto=$codproducto";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

	//***************************************
	//**** mantenimientos
	
	
	public function select_mov_ingsal($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT inv_producto.codproducto, producto,
				IFNULL(inv_producto.descripcion,'') AS descripcion,
				IFNULL(inv_producto.modelo,'') AS modelo,
				IFNULL(inv_producto.serief,'') AS serief,
				marca,  
				IFNULL(DATE_FORMAT(inv_transaccion.fecha,'%d/%m/%Y'),'') AS fecha,
				inv_transaccion.codtransaccion,
				inv_motivo.motivo,
				inv_tipo.tipodsc,
				CASE inv_motivo.tipo WHEN 's' THEN 'Salida' ELSE 'Ingreso' END AS mov,
				GROUP_CONCAT(CONCAT_WS('->',IFNULL(tipodsc,producto),modelo,serief,cantidad,'Und.')) AS detalle,
				
				GROUP_CONCAT(CONCAT(IFNULL(tipodsc,producto),':: IMEI / CCI ',imei,' :: MODELO: ',modelo,' :: MARCA / OPERADORA: ',marca,' :: NUMERO',numero)) AS detalle2
				
				FROM inv_producto LEFT JOIN inv_marca ON  inv_producto.codmarca=inv_marca.codmarca
					INNER JOIN inv_dettransaccion ON inv_producto.codproducto=inv_dettransaccion.codproducto AND flgactivo='1'
					INNER JOIN inv_transaccion ON inv_dettransaccion.codtransaccion=inv_transaccion.codtransaccion
					INNER JOIN inv_motivo ON inv_transaccion.codmotivo=inv_motivo.codmotivo
					INNER JOIN inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo
				WHERE inv_transaccion.flag='1' and inv_dettransaccion.flag='1' and inv_producto.flag='1' 
					AND inv_motivo.tipo IN ('s') $searchQuery 
				GROUP BY inv_transaccion.codtransaccion  ";
				// (i,s)
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_total_mov_ingsal($searchQuery=null){
		$sql=" SELECT COUNT(DISTINCT inv_transaccion.codtransaccion) AS total 
				FROM inv_producto LEFT JOIN inv_marca ON  inv_producto.codmarca=inv_marca.codmarca
					INNER JOIN inv_dettransaccion ON inv_producto.codproducto=inv_dettransaccion.codproducto AND flgactivo='1'
					INNER JOIN inv_transaccion ON inv_dettransaccion.codtransaccion=inv_transaccion.codtransaccion
					INNER JOIN inv_motivo ON inv_transaccion.codmotivo=inv_motivo.codmotivo
					INNER JOIN inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo
				WHERE inv_transaccion.flag='1' AND inv_dettransaccion.flag='1' AND inv_producto.flag='1' 
					AND inv_motivo.tipo IN ('s')  $searchQuery " ;
				
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function select_producto_mant($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT inv_producto.*, 
				IFNULL(inv_producto.descripcion,'') AS descripcion,
				IFNULL(inv_producto.modelo,'') AS modelo,
				IFNULL(inv_producto.serief,'') AS serief,
				marca,  
				IFNULL(DATE_FORMAT(m.fecha,'%d/%m/%Y'),'') AS fechamant,
				IFNULL(DATE_FORMAT(inv_producto.fechainicio,'%d/%m/%Y'),'') AS programado,
				case when ifnull(inv_producto.fechainicio,'')='' then ''
				else to_days(inv_producto.fechainicio) - to_days(now())
				end as dias,
				to_days(inv_producto.fechainicio) - to_days(now()) + 10000 as dias2,
				
				IFNULL(inv_transaccion.usuariodestino,'NO ASIGNADO') AS usuariodestino, 
				IFNULL(DATE_FORMAT(m.fechadevuelve,'%d/%m/%Y'),'') AS fechadevuelve,
				IFNULL(m.usuariodestino,'') AS destino,
				IFNULL(m.codtransaccion,'') AS codmantenimiento,
				concat_ws('_',inv_producto.codproducto,m.codtransaccion) as llave,
				inv_tipo.tipodsc
				FROM inv_producto 
					LEFT JOIN inv_marca ON  inv_producto.codmarca=inv_marca.codmarca
					INNER JOIN inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo
					LEFT JOIN inv_mantenimiento m ON inv_producto.codproducto=m.codproducto AND flgregreso='0'
					LEFT JOIN inv_dettransaccion ON inv_producto.codproducto=inv_dettransaccion.codproducto AND flgactivo='1'
					LEFT JOIN inv_transaccion ON inv_dettransaccion.codtransaccion=inv_transaccion.codtransaccion
				WHERE flgmantenimiento='1' and inv_producto.flag='1'  $searchQuery 
					and inv_producto.codproducto not in (select codproducto from inv_transaccion t inner join inv_dettransaccion d
						on t.codtransaccion=d.codtransaccion inner join inv_motivo m on t.codmotivo=m.codmotivo
						where t.flag='1' and d.flag='1' and m.tipo='s'
					) ";
		$sql.=" group by inv_producto.codproducto
				HAVING usuariodestino!='NO ASIGNADO'
				order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;

		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	// total de registros por auditor fecha
	public function selec_total_producto_mant($searchQuery=null){
		$sql=" SELECT COUNT(DISTINCT inv_producto.codproducto) AS total 
				FROM inv_producto LEFT JOIN inv_marca ON  inv_producto.codmarca=inv_marca.codmarca
					LEFT JOIN inv_mantenimiento m ON inv_producto.codproducto=m.codproducto AND flgregreso='0'
					INNER JOIN inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo
					LEFT JOIN inv_dettransaccion ON inv_producto.codproducto=inv_dettransaccion.codproducto AND flgactivo='1'
					LEFT JOIN inv_transaccion ON inv_dettransaccion.codtransaccion=inv_transaccion.codtransaccion
					
				WHERE flgmantenimiento='1' and inv_producto.flag='1'  $searchQuery 
					and inv_producto.codproducto not in (select codproducto from inv_transaccion t inner join inv_dettransaccion d
						on t.codtransaccion=d.codtransaccion inner join inv_motivo m on t.codmotivo=m.codmotivo
						where t.flag='1' and d.flag='1' and m.tipo='s'
					)
					AND inv_transaccion.usuariodestino is not null " ;
				
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	
	public function select_movimiento($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT inv_producto.*, 
				IFNULL(inv_producto.descripcion,'') AS descripcion,
				IFNULL(inv_producto.modelo,'') AS modelo,
				IFNULL(inv_producto.serief,'') AS serief,
				
				marca,  
				IFNULL(inv_transaccion.usuariodestino,'NO ASIGNADO') AS usuariodestino, 
				IFNULL(DATE_FORMAT(inv_transaccion.fecha,'%d/%m/%Y'),'') AS fecha,
				IFNULL(DATE_FORMAT(m.fecha,'%d/%m/%Y'),'') AS fechamant,
				IFNULL(DATE_FORMAT(m.fechadevuelve,'%d/%m/%Y'),'') AS fechadevuelve,
				IFNULL(m.usuariodestino,'') AS destino,
				CONCAT_WS('_',inv_producto.codproducto,IFNULL(m.codtransaccion,0),IFNULL(inv_transaccion.codtransaccion,0),IFNULL(inv_dettransaccion.coddetalle,0)) AS llave,
				IFNULL(m.codtransaccion,'') AS codmantenimiento,
				IFNULL(inv_transaccion.codtransaccion,'') AS codasignacion,
				DATE_FORMAT(DATE_ADD(IFNULL(vista.fecha,inv_transaccion.fecha), INTERVAL periodo DAY),'%d/%m/%Y') AS proximo
				
				FROM inv_producto LEFT JOIN inv_marca ON  inv_producto.codmarca=inv_marca.codmarca
					LEFT JOIN inv_dettransaccion ON inv_producto.codproducto=inv_dettransaccion.codproducto AND flgactivo='1'
					LEFT JOIN inv_transaccion ON inv_dettransaccion.codtransaccion=inv_transaccion.codtransaccion
					LEFT JOIN inv_mantenimiento m ON inv_producto.codproducto=m.codproducto AND flgregreso='0'
					LEFT JOIN (SELECT MAX(fechadevuelve) AS fecha,codproducto 
						FROM inv_mantenimiento
						WHERE flag='1' AND flgregreso='1'  
						GROUP BY codproducto) AS vista ON inv_producto.codproducto=vista.codproducto
					
				WHERE inv_producto.flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	public function selec_one_movimiento($codtransaccion){
		
		$sql="SELECT inv_mantenimiento.*, 
						date_format(fecha,'%d/%m/%Y') as fechaf, 
						date_format(fechadevuelve,'%d/%m/%Y') as fechadevuelvef,
				inv_motivo.motivo
				FROM inv_mantenimiento inner join inv_motivo on inv_mantenimiento.codmotivo=inv_motivo.codmotivo
				where codtransaccion=$codtransaccion ";
				
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	
	public function insert_mantenimiento($codproducto,$fecha,$usuariodestino,$descripcion,$codmotivo,$tipom,$flgregreso,$id_pais,$usuario,$ip){

        $sql="insert into inv_mantenimiento(codmotivo,tipom,flgregreso,descripcion,fecha,usuariodestino,codproducto,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values($codmotivo,'$tipom','$flgregreso','$descripcion','$fecha','$usuariodestino','$codproducto','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	
	
	public function update_mantenimiento($id,$codproducto,$fecha,$usuariodestino,$descripcion,$codmotivo,$tipom,$usuario,$ip){
	   
        $sql="update inv_mantenimiento 
				set 
					tipom='$tipom',
					descripcion='$descripcion',
					usuariodestino='$usuariodestino',
					fecha='$fecha',
					codmotivo=$codmotivo,
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codtransaccion=$id ";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	
	public function delete_movimiento($codtransaccion){
		$sql="update inv_transaccion set flag='0' where codtransaccion=$codtransaccion ";
		$consulta=$this->db->execute($sql);
		
		$sql="update inv_dettransaccion set flag='0' where codtransaccion=$codtransaccion ";
		$consulta=$this->db->execute($sql);
		
        return $consulta;
		
	}
	
	public function update_fecproxmant($fechainicio,$codproducto,$usuario,$ip){
		$sql="update inv_producto 
				set 
					fechainicio='$fechainicio',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codproducto=$codproducto ";
		
		$consulta=$this->db->execute($sql);
		 return $consulta;
	}
	
	public function update_backmantenimiento($id,$codproducto,$flgregreso,$fecha,$descripcionback,$usuarioretorno,$usuario,$ip){
	 	
        $sql="update inv_mantenimiento 
				set 
					descripcionback='$descripcionback',
					fechadevuelve='$fecha',
					flgregreso='$flgregreso',
					usuarioretorno='$usuarioretorno',
					usuario_modifica='$usuario',
					fecha_modifica=now(),
					ip_modifica='$ip'
                where codtransaccion=$id ";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }
	
	public function selec_motivos($tipo,$flginformatica=null){
		unset($this->listas);
		$sql="SELECT codmotivo, motivo 
				from inv_motivo 
				where flag='1' ";
			if(!empty($tipo))
				$sql.=" and tipo='$tipo' ";
			if(!empty($flginformatica))
				$sql.=" and flginformatica='1' ";
			
			$sql.="	order by motivo" ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_sedes(){
		unset($this->listas);
		$sql="SELECT codsede, sede from inv_sede where flag='1' order by sede" ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_staff($id_pais){
		unset($this->listas);
		$sql="SELECT id_auditor, CONCAT_WS(' ', nombre,apepaterno,apematerno) AS fullauditor 
				FROM prg_auditor WHERE flag='1' AND id_pais='$id_pais' AND flgstatus='1' and id_auditor>0
				ORDER BY 2" ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;
		
	}
	
	public function selec_areas(){
		unset($this->listas);
		$sql="SELECT codarea, area from inv_area where flag='1' order by area" ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	public function selec_empresa($id_pais){
		unset($this->listas);
		$sql="SELECT codempresa, empresa from inv_empresa where flag='1' and id_pais='$id_pais' order by empresa" ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	// reporte kardex de producto
	public function selec_movimiento($codproducto){
		unset($this->listas);
		$sql="
			SELECT * FROM (
				SELECT '' as tipom, coddetalle,
					CASE  WHEN TO_DAYS(t.fecha) IS NULL THEN '' ELSE DATE_FORMAT(t.fecha,'%d/%m/%Y') END AS fecha, 
					IFNULL(TO_DAYS(t.fecha),'') AS dias,
					t.descripcion,m.motivo AS tipo, usuariodestino, cantidad, ifnull(t.archivo,'') as archivo
				FROM inv_dettransaccion d INNER JOIN inv_transaccion t ON d.codtransaccion=t.codtransaccion
					INNER JOIN inv_motivo m ON t.codmotivo=m.codmotivo 
				WHERE d.flag='1' AND codproducto=$codproducto AND tipo IN ('i','s')
				UNION
				SELECT '' as tipom, coddetalle,DATE_FORMAT(t.fecha,'%d/%m/%Y') AS fecha, to_days(t.fecha) as dias,
					-- CASE  WHEN flgactivo='0' THEN 'RETORNO EQUIPO' ELSE 'EQUIPO ASIGNADO' END AS descripcion,
					t.descripcion,
					m.motivo AS tipo,usuariodestino, cantidad, ifnull(t.archivo,'') as archivo
				FROM inv_dettransaccion d INNER JOIN inv_transaccion t ON d.codtransaccion=t.codtransaccion
					INNER JOIN inv_motivo m ON t.codmotivo=m.codmotivo 
				WHERE d.flag='1' AND codproducto=$codproducto  AND tipo IN ('x') 
				UNION
				SELECT ifnull(tipom,'') as tipom, codtransaccion, DATE_FORMAT(m.fecha,'%d/%m/%Y') AS fecha,   to_days(m.fecha) as dias,
					m.descripcion, CONCAT_WS(' ','Salida',motivo) AS tipo,usuariodestino, 1 cantidad, ''
				FROM inv_mantenimiento m INNER JOIN inv_motivo t ON m.codmotivo=t.codmotivo
				WHERE m.flag='1' AND codproducto=$codproducto
				UNION
				SELECT ifnull(tipom,'') as tipom,codtransaccion, DATE_FORMAT(m.fechadevuelve,'%d/%m/%Y') AS fecha,   to_days(m.fechadevuelve) as dias,
					m.descripcionback, CONCAT_WS(' ','Reingreso',motivo) AS tipo,usuarioretorno, 1 cantidad,''
				FROM inv_mantenimiento m INNER JOIN inv_motivo t ON m.codmotivo=t.codmotivo
				WHERE m.flag='1' AND codproducto=$codproducto AND flgregreso=1
				UNION
				SELECT '' AS tipom,d.coddevolucion, DATE_FORMAT(d.fecha,'%d/%m/%Y') AS fecha,   TO_DAYS(d.fecha) AS dias,
					t.descripcion, 'Devolucion' AS tipo,CONCAT_WS(' a ',fullusuario, 'Almacen' ) fullusuario , 
					1 cantidad, ifnull(d.archivo,'') as archivo
				FROM inv_devolucion d INNER JOIN inv_dettransaccion t ON d.coddevolucion=t.coddevolucion
					INNER JOIN inv_usuario u ON d.codusuario=u.codusuario
				WHERE d.flag='1' AND codproducto=$codproducto AND t.flag='1'
			) AS vista 
			ORDER BY 4, 2 " ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;
		
	}
	
	/*******************
	MAESTROS DE USUARIOS
	***********************/
		
	public function select_usuarios($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT u.*, ifnull(a.area,'') as area, ifnull(s.sede,'') as sede
				FROM inv_usuario u left join 
					inv_area a on u.codarea=a.codarea left JOIN
					inv_sede s on u.codsede=s.codsede
				WHERE u.flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_usuario($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            inv_usuario u
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_usuario($codusuario){
		
		$sql="SELECT * from inv_usuario where codusuario=$codusuario ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_usuario($id_auditor,$dni,$fullusuario,$nombres,$apepaterno,$apematerno,$codarea,$codsede,$email,$usuario,$ip){

        $sql="insert into inv_usuario(id_auditor,dni,fullusuario,nombres,apepaterno,apematerno,codarea,codsede,email,flag,
				usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$id_auditor','$dni','$fullusuario','$nombres','$apepaterno','$apematerno','$codarea','$codsede','$email','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_usuario($codusuario,$id_auditor,$dni,$fullusuario,$nombres,$apepaterno,$apematerno,$codarea,$codsede,$email,$usuario,$ip){
	   
        $sql="update inv_usuario 
				set 
					dni='$dni',
					id_auditor='$id_auditor',
					fullusuario='$fullusuario',
					codsede='$codsede',
					email='$email',
					nombres='$nombres',
					apepaterno='$apepaterno',
					apematerno='$apematerno',
					codarea='$codarea',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codusuario=$codusuario";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_usuario($codusuario){
	   
        $sql="update inv_usuario set flag='0' where codusuario=$codusuario";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	/*******************
	MAESTROS DE AREAS
	***********************/
		
	public function select_areas($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *
				FROM inv_area
				WHERE flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	// total de registros por auditor fecha
	public function selec_total_area($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            inv_area 
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_area($codarea){
		
		$sql="SELECT * from inv_area where codarea=$codarea ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_area($area,$usuario,$ip){

        $sql="insert into inv_area(area,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$area','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_area($codarea,$area,$usuario,$ip){
	   
        $sql="update inv_area 
				set area='$area',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codarea=$codarea";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_area($codarea){
	   
        $sql="update inv_area set flag='0' where codarea=$codarea";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	/*******************
	MAESTROS DE MARCAS
	***********************/
		
	public function select_marcas($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *
				FROM inv_marca
				WHERE flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	// total de registros por auditor fecha
	public function selec_total_marca($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            inv_marca 
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_marca($codmarca){
		
		$sql="SELECT * from inv_marca where codmarca=$codmarca ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_marca($marca,$flgcelular,$flgit,$usuario,$ip){

        $sql="insert into inv_marca(marca,flgcelular,flgit,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$marca','$flgcelular','$flgit','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_marca($codmarca,$marca,$flgcelular,$flgit,$usuario,$ip){
	   
        $sql="update inv_marca 
				set marca='$marca',
					flgcelular='$flgcelular',
					flgit='$flgit',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codmarca=$codmarca";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_marca($codmarca){
	   
        $sql="update inv_marca set flag='0' where codmarca=$codmarca";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	/*******************
	MAESTROS DE CONSUMIBLE
	***********************/
		
	public function update_estadoproducto($codproducto,$flgactivo){
		$sql="update  inv_producto set activo='$flgactivo' where codproducto=$codproducto";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
	}	
		
	public function select_consumible($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT inv_producto.*, tipodsc
				FROM inv_producto inner join inv_tipo on inv_producto.codtipo=inv_tipo.codtipo
				WHERE inv_producto.flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	// total de registros por auditor fecha
	public function selec_total_consumible($searchQuery=null){
		$sql=" SELECT COUNT(inv_producto.codproducto) AS total 
			FROM 
            inv_producto inner join inv_tipo on inv_producto.codtipo=inv_tipo.codtipo
        WHERE inv_producto.flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_consumible($codproducto){
		
		$sql="SELECT * from inv_producto where codproducto=$codproducto ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_consumible($producto,$codtipo,$umedida,$stock_minimo,$codigo,$usuario,$ip){

        $sql="insert into inv_producto(producto,codtipo,umedida,stock_min,codigo,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$producto',$codtipo,'$umedida','$stock_minimo','$codigo','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_consumible($codproducto,$producto,$codtipo,$umedida,$stock_minimo,$codigo,$usuario,$ip){
	   
        $sql="update inv_producto 
				set 
					producto='$producto',
					codtipo=$codtipo,
					umedida='$umedida',
					stock_min=$stock_minimo,
					codigo='$codigo',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codproducto=$codproducto";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_consumible($codproducto){
	   
        $sql="update inv_producto set flag='0' where codproducto=$codproducto";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	/*******************
	MAESTROS DE SEDES
	***********************/
		
	public function select_sedes($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *
				FROM inv_sede
				WHERE flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	// total de registros por auditor fecha
	public function selec_total_sede($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            inv_sede 
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_sede($codsede){
		
		$sql="SELECT * from inv_sede where codsede=$codsede ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_sede($sede,$usuario,$ip){

        $sql="insert into inv_sede(sede,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$sede','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_sede($codsede,$sede,$usuario,$ip){
	   
        $sql="update inv_sede 
				set sede='$sede',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codsede=$codsede";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_sede($codsede){
	   
        $sql="update inv_sede set flag='0' where codsede=$codsede";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	
	/*******************
	MAESTROS DE TIPO DE PRODUCTO
	***********************/
		
	public function select_tipoproducto($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT codtipo, tipodsc,categoria, flgmantenimiento,tiempo ,
				CASE categoria WHEN 'a' THEN 'ADMINSTRACION' 
					WHEN 'i' THEN 'TECNOLOGIA' 
					WHEN 'c' THEN 'CELULARES'
					ELSE '' END AS categoriadsc,
				CASE flgmantenimiento WHEN '1' THEN 'SI' ELSE 'NO' END AS flgmantenimientodsc,
				CASE flgdato WHEN '1' THEN 'SI' ELSE 'NO' END AS dato
				FROM inv_tipo
				WHERE flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	// total de registros por auditor fecha
	public function selec_total_tipoproducto($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            inv_tipo 
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_tipoproducto($codtipo){
		
		$sql="SELECT * from inv_tipo where codtipo=$codtipo ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_tipoproducto($flgdato,$tipodsc,$flgmantenimiento,$categoria,$tiempo,$usuario,$ip){

        $sql="insert into inv_tipo(flgdato,tipodsc,flgmantenimiento,categoria,tiempo,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$flgdato','$tipodsc','$flgmantenimiento','$categoria','$tiempo','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_tipoproducto($codtipo,$flgdato,$tipodsc,$flgmantenimiento,$categoria,$tiempo,$usuario,$ip){
	   
        $sql="update inv_tipo 
				set tipodsc='$tipodsc',
					flgmantenimiento='$flgmantenimiento',
					categoria='$categoria',
					tiempo='$tiempo',
					flgdato='$flgdato',
					usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codtipo=$codtipo";
				
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_tipoproducto($codtipo){
	   
        $sql="update inv_tipo set flag='0' where codtipo=$codtipo";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	
	/*****************************************
		suministros
	******************************************/
	
	public function select_suministros($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT  p.codigo,p.producto, p.codproducto, 
				IFNULL(umedida,'') umedida, d.coddetalle,
				tipodsc, d.cantidad, IFNULL(d.precio,'') AS precio, 
				d.moneda,IFNULL(comprobante,'') comprobante,
				IFNULL(d.cantidad*d.precio,'') AS  subtotal,
				tr.proveedor2 as proveedor, DATE_FORMAT(tr.fecha,'%d/%m/%Y') AS fechacompraf, 
				IFNULL(tr.fecha_ingreso,'') AS fecha_ingreso,
				tr.codtransaccion,
				to_days(tr.fecha_ingreso) as dias
			FROM inv_producto p INNER JOIN 
				inv_dettransaccion d ON p.codproducto=d.codproducto INNER JOIN 
				inv_tipo t ON p.codtipo=t.codtipo INNER JOIN
				inv_transaccion tr ON d.codtransaccion=tr.codtransaccion
				inner join inv_motivo on tr.codmotivo=inv_motivo.codmotivo
			WHERE p.flag='1' AND tr.flag='1' and flgactivo='1' and p.activo='1' AND d.flag='1'  $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	// total de registros por auditor fecha
	public function selec_total_suministros($searchQuery=null){
		$sql=" SELECT COUNT(d.coddetalle) AS total 
				FROM inv_producto p INNER JOIN 
				inv_dettransaccion d ON p.codproducto=d.codproducto INNER JOIN 
				inv_tipo t ON p.codtipo=t.codtipo INNER JOIN
				inv_transaccion tr ON d.codtransaccion=tr.codtransaccion
				inner join inv_motivo on tr.codmotivo=inv_motivo.codmotivo
				WHERE p.flag='1' and p.activo='1' AND tr.flag='1' AND d.flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	 public function delete_detalle($coddetalle){
	   
        $sql="update inv_dettransaccion set flag='0' where coddetalle=$coddetalle";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	public function select_consumible_noselect($id_pais){
		unset($this->listas);
		$sql="SELECT inv_producto.codproducto, inv_producto.producto, tipodsc, umedida, IFNULL(vista.cantidad,0) AS cantidad
				FROM inv_producto 
					INNER JOIN inv_tipo ON inv_producto.codtipo=inv_tipo.codtipo INNER JOIN
					
					(SELECT SUM(CASE tipo WHEN 's' THEN  cantidad*-1 
						WHEN 'x' THEN  cantidad*-1
						WHEN 'i' THEN cantidad ELSE 0 END) AS cantidad, codproducto 
						FROM inv_dettransaccion d INNER JOIN inv_transaccion t ON d.codtransaccion=t.codtransaccion
							INNER JOIN inv_motivo m ON t.codmotivo=m.codmotivo
					WHERE d.flag='1' AND  t.flag='1' 
					GROUP BY codproducto) AS vista ON inv_producto.codproducto=vista.codproducto
											
				WHERE inv_producto.id_pais='$id_pais' AND inv_producto.flag='1'   AND categoria='a'
				ORDER BY producto " ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	
	public function select_suministros_repor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$having=''){
		unset($this->listas);
		$sql="SELECT  p.codigo,p.producto, p.codproducto, IFNULL(umedida,'') umedida,
				tipodsc,stock_min,
				SUM(CASE tipo WHEN 'x' THEN cantidad*-1 
							WHEN 's' THEN cantidad*-1 WHEN 'i' THEN cantidad ELSE 0 END) AS cantidad,
				MAX(CASE tr.codmotivo WHEN 10 THEN tr.fecha  END) AS fechaadq
				
			FROM inv_producto p INNER JOIN 
				inv_tipo t ON p.codtipo=t.codtipo LEFT JOIN
				inv_dettransaccion d  ON p.codproducto=d.codproducto LEFT JOIN 
					inv_transaccion tr ON d.codtransaccion=tr.codtransaccion AND d.flag='1' AND tr.flag='1' LEFT JOIN
				inv_motivo ON tr.codmotivo=inv_motivo.codmotivo
					
			WHERE p.flag='1' and p.activo='1'   $searchQuery 
			GROUP BY p.codproducto 
			$having ";
		
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
		
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	
	// total de registros por auditor fecha
	public function select_total_suministros_repor($searchQuery=null,$having=''){
		$sql="SELECT COUNT(*) AS total FROM (
				SELECT  DISTINCT p.codproducto AS codproducto,
				stock_min,
				SUM(CASE tipo WHEN 'x' THEN cantidad*-1 
							WHEN 's' THEN cantidad*-1 WHEN 'i' THEN cantidad ELSE 0 END) AS cantidad
				FROM inv_producto p INNER JOIN 
					inv_tipo t ON p.codtipo=t.codtipo LEFT JOIN
					inv_dettransaccion d  ON p.codproducto=d.codproducto LEFT JOIN 
						inv_transaccion tr ON d.codtransaccion=tr.codtransaccion AND d.flag='1' AND tr.flag='1' LEFT JOIN
					inv_motivo ON tr.codmotivo=inv_motivo.codmotivo
				WHERE p.flag='1' and p.activo='1' $searchQuery
				GROUP  BY p.codproducto
				$having
				) AS vista" ;
			
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function buscarcodigo_producto($codigo){
		$sql=" SELECT *
				FROM inv_producto 
				WHERE flag='1' and codigo='$codigo'
				AND codproducto NOT IN (
				SELECT  codproducto FROM inv_transaccion t INNER JOIN inv_dettransaccion d ON t.codtransaccion=d.codtransaccion
				INNER JOIN inv_motivo ON t.codmotivo=inv_motivo.codmotivo AND tipo='s'
				WHERE t.flag='1' AND d.flag='1' )				" ;
			
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
}
?>