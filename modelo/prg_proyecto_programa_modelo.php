<?php
class prg_proyecto_programa_model{
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

	public function select_proyectoprograma($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		

		$sql="SELECT prg_proyecto.id_proyecto, prg_proyecto.project_id, trim(proyect) as proyect, city, 
						state, country, 
						telephone, mobile, fax, email, modules, 
						case flg_cronograma when '1' then 'Si' else 'No' end as dsccronograma,
						GROUP_CONCAT(distinct programa SEPARATOR '<br>') AS programas,
						GROUP_CONCAT(distinct programa SEPARATOR ', ') AS programasex,
						sum(ifnull(prg_proyecto_detalle.montototal,0)) as montototal,
						SUM(ifnull(vista.montodetalle,0)) AS montodetalle,
						count(distinct prg_proyecto_detalle.coddetalle) as pago,
						(
						case when
								sum(
									ifnull(prg_proyecto_detalle.montototal,0)
									)
									!=
								SUM(
									ifnull(vista.montodetalle, 0)
									)
								 then 'Error'
							 when
								sum(
									ifnull(prg_proyecto_detalle.montototal,0)
									)
									!=
								SUM(
									ifnull(vista2.montocrono, 0)
									)
								 then 'Error '
							 else ''
							end
						) AS inconsistencia
					FROM prg_proyecto left JOIN 
						 prg_proyecto_programa ON prg_proyecto.project_id= prg_proyecto_programa.project_id	left join 
						 prg_proyecto_detalle on prg_proyecto.id_proyecto=prg_proyecto_detalle.id_proyecto
					LEFT JOIN 
					(
						SELECT coddetalle, 
							SUM(IFNULL(montofee,0) + IFNULL(montofeecert,0) + IFNULL(montoservicio,0) + IFNULL(montocourier,0) + IFNULL(montoviatico,0)
							+ IFNULL(cartacor,0)+ IFNULL(analisis,0)+ IFNULL(cursos,0)
							) AS montodetalle
						FROM prg_programacosto
						WHERE  flag='1'  
						GROUP BY coddetalle  
					) AS vista ON prg_proyecto_detalle.coddetalle=vista.coddetalle  
					left join (
						SELECT coddetalle, 
							SUM(IFNULL(importe,0)) AS montocrono
						FROM prg_cronogramapago
						WHERE  flag='1'  
						GROUP BY coddetalle  
					) AS vista2 ON prg_proyecto_detalle.coddetalle=vista2.coddetalle  
					WHERE prg_proyecto_detalle.flag='1' and prg_proyecto.flag = '1'  and prg_proyecto.project_id!='' and prg_proyecto.project_id is not null  $searchQuery ";
		$sql.="GROUP BY prg_proyecto.id_proyecto ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;

		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}
	
	
	// total de registros por auditor fecha
	public function selec_total_proyectoprograma($searchQuery=null){
		$sql=" SELECT COUNT(DISTINCT prg_proyecto.project_id) AS total 
			FROM prg_proyecto left JOIN prg_proyecto_detalle on prg_proyecto.id_proyecto=prg_proyecto_detalle.id_proyecto
        WHERE prg_proyecto.flag='1' $searchQuery ";
		
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function selec_one_proyectoprograma($id_proyecto,$id_pais){
		
			$sql="SELECT id_proyecto, prg_proyecto.project_id, proyect, prg_proyecto.city, prg_proyecto.state, 
			prg_proyecto.country, prg_proyecto.telephone, prg_proyecto.mobile, prg_proyecto.fax, prg_proyecto.email, modules, 
             insp_type,ruc,
			GROUP_CONCAT(programa SEPARATOR '<br>') AS programas
			FROM prg_proyecto left JOIN 
				 prg_proyecto_programa ON prg_proyecto.project_id= prg_proyecto_programa.project_id left join
				 prg_proyecto_importar on prg_proyecto.project_id=prg_proyecto_importar.project_id  AND prg_proyecto_programa.id_pais = '$id_pais'
			WHERE prg_proyecto.flag = '1' AND prg_proyecto.id_pais = '$id_pais' and prg_proyecto.id_proyecto=$id_proyecto 
			GROUP BY prg_proyecto.project_id ";
		
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	public function selec_proyectoxprograma($id_proyecto){
		unset($this->listas);
		$sql="SELECT CONCAT_WS('=>',iniciales, descripcion) AS programa FROM prg_proyecto_programa INNER JOIN 
				prg_proyecto ON prg_proyecto_programa.project_id=prg_proyecto.project_id AND
				prg_proyecto_programa.id_pais=prg_proyecto.id_pais
				INNER JOIN prg_programa ON prg_proyecto_programa.programa=prg_programa.iniciales
					AND  prg_proyecto_programa.id_pais=prg_programa.id_pais
			WHERE 	prg_proyecto.id_proyecto=$id_proyecto 
					order by programa ";
		
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
    
		
	}
	
	public function selec_one_vendedor($coddetalle){
		$sql="SELECT CONCAT_WS(' ',a.codigo,u.nombres) AS nombre
			FROM prg_proyecto_detalle d INNER JOIN prg_usuarios u ON d.codejecutivo=u.id_usuario
			INNER JOIN prg_auditor a ON u.id_auditor=a.id_auditor
			WHERE d.coddetalle=$coddetalle ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
			
			
	
	public function selec_anios_detalle_proyectoprograma($id_proyecto,$id_pais){
		unset($this->listas);
		$sql="SELECT distinct anio
					FROM  prg_proyecto_detalle LEFT JOIN prg_estadoproyecto ON prg_proyecto_detalle.codestado =prg_estadoproyecto.codestado
					WHERE id_proyecto=$id_proyecto  AND prg_proyecto_detalle.flag='1'
					ORDER BY anio";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function delete_one_proyectoprograma($id_proyecto,$coddetalle,$id_pais,$ip,$usuario){
		$sql="update prg_proyecto_detalle 
				set flag='0', fecha_modifica=now(), ip_modifica='$ip', usuario_modifica='$usuario'
				WHERE id_proyecto=$id_proyecto  AND coddetalle=$coddetalle";
		$consulta=$this->db->execute($sql);
		
		$sql="update prg_cronogramapago 
				set flag='0', fecha_modifica=now(), ip_modifica='$ip', usuario_modifica='$usuario'
				WHERE id_proyecto=$id_proyecto  AND coddetalle=$coddetalle";
		$consulta=$this->db->execute($sql);
		
		$sql="update prg_programacosto 
				set flag='0', fecha_modifica=now(), ip_modifica='$ip', usuario_modifica='$usuario'
				WHERE project_id=$id_proyecto  AND coddetalle=$coddetalle";
		$consulta=$this->db->execute($sql);
	    
		return $consulta;
	}
	
	
	public function selec_detalle_proyectoprograma($id_proyecto,$id_pais){
		unset($this->listas);
		$sql="SELECT coddetalle,concat_ws(' ',IFNULL(prg_estadoproyecto.descripcion,'') ,anio) as detalle,mes ,anio
					FROM  prg_proyecto_detalle LEFT JOIN prg_estadoproyecto ON prg_proyecto_detalle.codestado =prg_estadoproyecto.codestado
					WHERE id_proyecto=$id_proyecto  AND prg_proyecto_detalle.flag='1'
					ORDER BY anio desc,mes desc,2";
	
		$sql="
		SELECT prg_proyecto_detalle.coddetalle,
		CONCAT_WS(' ',IFNULL(prg_estadoproyecto.descripcion,'') ,anio) AS detalle,mes ,anio, 
		ifnull(vista.programa,'') as programa,
		prg_proyecto_detalle.observacion
		FROM  prg_proyecto_detalle LEFT JOIN 
			prg_estadoproyecto ON prg_proyecto_detalle.codestado =prg_estadoproyecto.codestado LEFT JOIN
			(
			SELECT GROUP_CONCAT(DISTINCT programa) AS programa,coddetalle
				FROM prg_programacosto 
				WHERE flag='1'
				GROUP BY coddetalle  
				ORDER BY programa
			) AS vista ON prg_proyecto_detalle.coddetalle=vista.coddetalle
		WHERE id_proyecto=$id_proyecto  AND prg_proyecto_detalle.flag='1'
		ORDER BY anio DESC,mes DESC,2";

		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	
	
	public function selec_progCostobyProy($coddetalle,$id_proyecto,$id_pais){
		unset($this->listas);
		$sql="select *
				from prg_programacosto 
				where flag='1' and project_id=$id_proyecto and coddetalle='$coddetalle'
				order by programa ";
				
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function selec_one_progCostobyProy($id_costo,$coddetalle,$id_proyecto,$id_pais){
		
		$sql="select *
				from prg_programacosto 
				where flag='1' and project_id=$id_proyecto and coddetalle=$coddetalle and id_costo=$id_costo";
			
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
	}


	
	public function selec_one_detalle_proyecto($id_proyecto,$coddetalle,$id_pais){
		$sql="SELECT  
				anio,observacion,igv,
				case is_analisis when '1' then 'Si' else 'No' end as is_analisis,
				case prg_proyecto_detalle.is_viatico when '1' then 'Si' else 'No' end as is_viatico,
					case prg_proyecto_detalle.is_igv when '1' then 'Si' else 'No' end as is_igv,
					case prg_proyecto_detalle.is_curso when '1' then 'Si' else 'No' end as is_curso,
					case prg_proyecto_detalle.tipofactura when 'L' then 'Local' else 'Importado' end as tipofactura,
				analisisdsc,mes,montototal,moneda,analisisdsc,
				ifnull(prg_condicionpago.descripcion,'') as condicionpago,
				ifnull(prg_usuarios.nombres,'') as comercial,
				ifnull(prg_estadoproyecto.descripcion,'') as estado,
				ifnull(impuesto,0) as impuesto
				FROM prg_proyecto INNER JOIN 
					prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto left join
					prg_condicionpago on prg_proyecto_detalle.id_condicion=prg_condicionpago.id_condicion left join 
					prg_usuarios on prg_proyecto_detalle.codejecutivo=prg_usuarios.id_usuario left join 
					prg_estadoproyecto on prg_proyecto_detalle.codestado=prg_estadoproyecto.codestado
				WHERE prg_proyecto.flag = '1' AND prg_proyecto.id_pais = '$id_pais'
				and prg_proyecto.id_proyecto=$id_proyecto 
				and prg_proyecto_detalle.coddetalle=$coddetalle";
		$consulta=$this->db->consultarOne($sql);
		
        return $consulta;	
	}
	
	public function selec_one_detalle_proyectobyId($coddetalle){
		$sql="SELECT * from prg_proyecto_detalle where prg_proyecto_detalle.coddetalle=$coddetalle";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
	}
	
	public function selec_cronogramapago($id_proyecto,$coddetalle,$id_pais){
		$sql="select *, DATE_FORMAT(fecha,'%d/%m/%Y') as fecha_f ,
						DATE_FORMAT(fechafactura,'%d/%m/%Y') as fechafactura_f ,
						DATE_FORMAT(fechanc,'%d/%m/%Y') as fechanc_f ,
						DATE_FORMAT(fechacobro,'%d/%m/%Y') as fechacobro_f ,
						DATE_FORMAT(fechavencimiento,'%d/%m/%Y') as fechavencimiento_f,
						case cobrado when '1' then 'Si' else 'No' end as dsccobrado,
						case noemail when '1' then 'Si' else 'No' end as dscnoemail
						
				from prg_cronogramapago 
				where flag='1' and id_proyecto=$id_proyecto and coddetalle=$coddetalle 
				order by fecha";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;		
	}
	
	public function selec_data_nota($id_pais){
		$sql="select descripcion , nota	from prg_cronogramapago_nota where flag='1' and id_pais='$id_pais' order by descripcion";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;		
	}
	
	// localizacion valido para mexico
	public function selec_localizacion($id_pais){
		$sql="select codlocalizacion, concat_ws(' ',codigo,localizacion) as localizacion
				from ofi_localizacion
                where flag='1' and id_pais='$id_pais' order by 2 ";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;		
	}
	
	
	public function selec_one_cronogramapago($id_cronograma,$id_pais){
		$sql="select *, DATE_FORMAT(fecha,'%d/%m/%Y') as fecha_f ,
						DATE_FORMAT(fechafactura,'%d/%m/%Y') as fechafactura_f ,
						DATE_FORMAT(fechacobro,'%d/%m/%Y') as fechacobro_f ,
						DATE_FORMAT(fechavencimiento,'%d/%m/%Y') as fechavencimiento_f,
						case cobrado when '1' then 'Si' else 'No' end as dsccobrado,
						case noemail when '1' then 'Si' else 'No' end as dscnoemail,
						DATE_FORMAT(fecha_ofisis,'%d/%m/%Y %H:%i') as fecha_ofisis_f ,
						ifnull(nrofactura,'') as nrofacturaf,
						ifnull(numeronc,'') as numeronc,
						ifnull(montoservicio,'') as montoservicio,
						ifnull(montonc,'') as montonc,
						DATE_FORMAT(fechanc,'%d/%m/%Y') as fechanc_f ,
						ifnull(codlocalizacion,0) codlocalizacion
				from prg_cronogramapago 
				where id_cronograma=$id_cronograma";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
	}
	
	public function insert_proyectoprograma($descripcion,$id_pais,$usuario,$ip){

        $sql="insert into prg_estadoproyecto(descripcion,id_pais,flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$descripcion','$id_pais','1','$usuario',now(),'$ip','$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_cronogramapago($id_cronograma,$coddetalle,$id_proyecto,$nrofactura,$fechafactura,$fechavencimiento,$fechacobro,$cobrado,$noemail,$usuario,$ip,$id_pais,$dia){
	   
	    $dia = !empty($dia) ? $dia : 0;
	    //$fechavencimiento = !empty($fechavencimiento) ? "'$fechavencimiento'" : "NULL";
	    $fechafactura = !empty($fechafactura) ? "'$fechafactura'" : "NULL";
	    $fechacobro = !empty($fechacobro) ? "'$fechacobro'" : "NULL";
	   
        $sql="UPDATE prg_cronogramapago SET 
			fechafactura=$fechafactura,
			fechacobro=$fechacobro, ";	
				
		if(!empty($fechavencimiento)) $sql.=" fechavencimiento='$fechavencimiento', ";
		else if(!empty($fechafactura)) $sql.=" fechavencimiento=DATE_ADD($fechafactura, INTERVAL $dia DAY),";
		else  $sql.=" fechavencimiento=null, ";

		$sql.="	nrofactura='$nrofactura',
			cobrado='$cobrado',
			noemail='$noemail',
			usuario_modifica='$usuario',
			ip_modifica='$ip',
			fecha_modifica=now()
		 WHERE id_cronograma=$id_cronograma and id_proyecto=$id_proyecto and coddetalle=$coddetalle";
		 
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

	public function delete_cronogramapago($id_cronograma,$coddetalle,$id_proyecto,$usuario,$ip,$id_pais){
		 $sql="UPDATE prg_cronogramapago SET 
			fechafactura=null,
			fechacobro=null,
			fechavencimiento=null,
			nrofactura='',
			cobrado='0',
			noemail='0',
			usuario_modifica='$usuario',
			ip_modifica='$ip',
			fecha_modifica=now()
		 WHERE id_cronograma=$id_cronograma and id_proyecto=$id_proyecto  and coddetalle=$coddetalle";
		$consulta=$this->db->execute($sql);
        return $consulta;	
		
	}

    public function delete_estaproyecto($codestado){
	   
        $sql="update prg_estadoproyecto set flag='0' where codestado=$codestado";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	
	
	public function selec_one_condicionpago($coddetalle){
		$sql="SELECT dia from 
			prg_condicionpago inner join prg_proyecto_detalle on prg_proyecto_detalle.id_condicion=prg_condicionpago.id_condicion
		where coddetalle=$coddetalle";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
	}

	//*********************************************************************
	// TC FACTURAS
	//*********************************************************************
	public function selec_tc_facturabyProy($id_proyecto,$id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="select *, DATE_FORMAT(fecha,'%d/%m/%Y') as fecha_f ,DATE_FORMAT(fechacobro,'%d/%m/%Y') as fechacobro_f,
					case cobrado when '1' then 'Si' else 'No' end as dsccobrado,
					case noemail when '1' then 'Si' else 'No' end as dscnoemail
			from tc_factura 
			where flag='1' and id_proyecto=$id_proyecto
			order by fecha";
		$consulta=$this->db->consultar($sql);
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_one_factura_proyectobyId($codfactura,$id_proyecto){
		$sql="SELECT *, DATE_FORMAT(fecha,'%d/%m/%Y') as fecha_f ,DATE_FORMAT(fechacobro,'%d/%m/%Y') as fechacobro_f  
				from tc_factura where id_proyecto=$id_proyecto and codfactura=$codfactura";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;
	}
	
	public function update_facturaTc($codfactura,$coddetalle,$id_proyecto,$fechacobro,$cobrado,$noemail,$usuario,$ip,$id_pais){
		$fechacobro = !empty($fechacobro) ? "'$fechacobro'" : "NULL";
		
		$sql="UPDATE tc_factura SET
				   cobrado='$cobrado',
				   noemail='$noemail',
				   fechacobro=$fechacobro,
				   usuario_modifica='$usuario',
				   fecha_modifica=now(),
				   ip_modifica='$ip'
			WHERE codfactura=$codfactura and id_proyecto=$id_proyecto ";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
	}
	
	//*********************************************************************
	// TC DATOS
	//*********************************************************************
	
	public function selec_resulAnalisis($project_id,$id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT DISTINCT YEAR(fecha) AS anio ,month(fecha) mes
			FROM tc_datos 
			WHERE flag = '1' AND CAST(cu AS UNSIGNED)=CAST('$project_id' AS UNSIGNED) 
			order by fecha desc ";
		$consulta=$this->db->consultar($sql);
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	public function selec_TcByAnioMes($project_id,$anio,$mes,$sess_codpais){
		unset($this->listas);
		$sql="SELECT
                asistente,subprograma ,tipo ,trc ,itc ,traces ,opcion ,cu,proyecto,producto,pais_origen ,lote,
                pais_destino,volumen,grupo ,individual ,cui ,cliente ,modo_envio ,costo_eu ,costo_usd ,cos_courier_usd,
                nrotrk,codtc ,  date_format(fecha_emision,'%d/%m/%Y') as  fecha_emision ,date_format(fecha,'%d/%m/%Y') as fecha,
                monedacliente,faccliente,date_format(fechafacturacliente,'%d/%m/%Y') as fechafacturacliente,montocliente,
                ifnull(codfactura,0) codfactura
            FROM tc_datos 
            WHERE flag='1' AND montocliente>0	 and CAST(cu AS UNSIGNED)=CAST('$project_id' AS UNSIGNED) 
				and year(fecha_emision)='$anio'
				and month(fecha_emision)=$mes	
			order by codfactura , fecha desc";
			
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function insert_Tc_resultadoFactura($faccliente,$montocliente,$fecha,$monedacliente,$project_id,$id_proyecto,$usuario,$ip){
		$sql="insert into tc_factura(numero,monto,fecha,moneda,project_id,id_proyecto,usuario_ingreso,fecha_ingreso,ip_ingreso)
			values ('$faccliente','$montocliente','$fecha','$monedacliente','$project_id','$id_proyecto','$usuario',now(),'$ip')";
		$consulta=$this->db->executeIns($sql);
		
        return $consulta;
	}
	
	public function update_Tc_resultadoFactura($faccliente,$fecha,$monedacliente,$project_id,$codfactura,$anio,$mes,$cadena){
		$sql="UPDATE tc_datos SET
				faccliente='$faccliente' , 
				fechafacturacliente='$fecha',
				codfactura=$codfactura
        WHERE flag = '1' and CAST(cu AS UNSIGNED)=CAST('$project_id' AS UNSIGNED) 
				and year(fecha_emision)='$anio' and month(fecha_emision)='$mes'";
		$consulta=$this->db->execute($sql);
        return $consulta;
	}
	
	
	//*********************************************************************
	// LABORATORIO DE RESULTADOS
	//*********************************************************************
	public function selec_resulLaboratorio($project_id,$id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT DISTINCT YEAR(fecha) AS anio 
				FROM lab_resultado 
				WHERE flag = '1' AND CAST(project_id AS UNSIGNED)=CAST('$project_id' AS UNSIGNED) 
				order by 1 ";
		$consulta=$this->db->consultar($sql);

		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	public function selec_lab_resultadobyProy($project_id,$id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="select *, 
						case when fecha!='0000/00/00' then DATE_FORMAT(fecha,'%d/%m/%Y') end as fecha_f , 
						case when fechaentregacli!='0000/00/00' then  
							DATE_FORMAT(fechaentregacli,'%d/%m/%Y') end as fechaentregacli_f 
						from lab_resultado 
				where flag='1' and project_id='$project_id'
				order by fecha ";
				
		$consulta=$this->db->consultar($sql);	
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	

	public function selec_lab_resultadoByAnio($project_id,$anio,$id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT ifnull(codfactura,0) codfactura,  fecha,	codresultado,project_id,proyecto,
				pais,responsable,laboratorio,cultivo,producto,analisis,nrolaboratorio,
                nromuestra_cliente,nromuestracu,date_format(fecha,'%d/%m/%y') as fecha_f,resultado,nrofactura,
				preciodol,monedacliente, faccliente,montocliente,
               date_format(fechafacturacliente,'%d/%m/%Y') as   fechafacturacliente
              FROM lab_resultado
	      WHERE flag = '1' and CAST(project_id AS UNSIGNED)=CAST('$project_id' AS UNSIGNED) and year(fecha)='$anio'
		  order by codfactura desc, fecha desc ";
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	
	public function insert_lab_resultadoFactura($faccliente,$montocliente,$fecha,$monedacliente,$project_id,$id_proyecto,$usuario,$ip){
		$sql="insert into lab_factura(numero,monto,fecha,moneda,project_id,id_proyecto,usuario_ingreso,fecha_ingreso,ip_ingreso) values
			('$faccliente','$montocliente','$fecha','$monedacliente','$project_id','$id_proyecto','$usuario',now(),'$ip')";
		$consulta=$this->db->executeIns($sql);
        return $consulta;
	}
	
	public function update_lab_resultadoFactura($faccliente,$fecha,$monedacliente,$project_id,$codfactura,$anio,$cadena){
		$sql="UPDATE lab_resultado SET
			faccliente='$faccliente' , 
			monedacliente='$monedacliente',
			fechafacturacliente='$fecha',
			codfactura=$codfactura
		WHERE CAST(project_id AS UNSIGNED)=CAST('$project_id' AS UNSIGNED) and 
				year(fecha)='$anio' and codresultado in ($cadena)";
		$consulta=$this->db->execute($sql);
        return $consulta;
	}
	
	
	/************************************
	 LA SECCION DE PROYECTO COMERCIAL
	************************************/
	public function select_proyectocomercial($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage,$inconsistencia,$id_pais=null){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT 
                group_concat(distinct 
                case when ifnull(id_proyecto_adm,0)>0 then  concat_ws('->',project_id_adm,proyecto_adm) else '' end 
                ) proyecto_adm,
                prg_proyecto.id_proyecto, prg_proyecto.project_id, proyect, city, state, country, telephone, mobile, fax, 
                email, modules, 
				count(distinct prg_proyecto_detalle.coddetalle) as dsccronograma,
				(select ifnull(GROUP_CONCAT(distinct prg_proyecto_programa.programa SEPARATOR '<br>'),'') 
					from prg_proyecto_programa 
					where  prg_proyecto_programa.project_id=prg_proyecto.project_id and id_pais = '$id_pais' 
				) AS programas,
                SUM(ifnull(vista.montodetalle,0)) AS montodetalle,
                sum(ifnull(prg_proyecto_detalle.montototal,0)) as montototal
			FROM prg_proyecto left join 
                     prg_proyecto_detalle on prg_proyecto.id_proyecto=prg_proyecto_detalle.id_proyecto
						and prg_proyecto_detalle.flag='1'
				LEFT JOIN 
					(
						SELECT coddetalle, 
							SUM(IFNULL(montofee,0) + IFNULL(montofeecert,0) + IFNULL(montoservicio,0) + IFNULL(montocourier,0) + IFNULL(montoviatico,0)
							+ IFNULL(cartacor,0)+ IFNULL(analisis,0)+ IFNULL(cursos,0)
							) AS montodetalle
						FROM prg_programacosto
						WHERE  flag='1'  
						GROUP BY coddetalle  
					) AS vista ON prg_proyecto_detalle.coddetalle=vista.coddetalle   
			WHERE prg_proyecto.flag = '1' and prg_proyecto.project_id!='' and prg_proyecto.project_id is not null  $searchQuery ";
		$sql.="GROUP BY prg_proyecto.id_proyecto ";
		if($inconsistencia=='1') $sql.=" having  montototal<> montodetalle";	
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}

	public function select_productoxproyecto($id_pais,$project_id){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT codproducto,producto
						FROM prg_producto inner join prg_proyecto_producto on prg_producto.codproducto=prg_proyecto_producto.id_producto
						WHERE prg_producto.flag='1' AND prg_producto.id_pais='$id_pais'
							and prg_proyecto_producto.project_id='$project_id'
						ORDER BY producto" ;
	
		$consulta=$this->db->consultar($sql);
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
		
	}


	public function select_detallexproyecto($id_proyecto,$coddetalle){
		unset($this->listas);
		$sql="SELECT coddetalle, mes,anio,IFNULL(dsc_programaren,'') AS programa,
					prg_estadoproyecto.descripcion
					FROM prg_proyecto_detalle INNER JOIN 
						prg_estadoproyecto ON prg_proyecto_detalle.codestado =prg_estadoproyecto.codestado
					WHERE prg_proyecto_detalle.flag='1' AND prg_proyecto_detalle.id_proyecto=$id_proyecto ";
				if(!empty($coddetalle)) $sql.=" and coddetalle!=$coddetalle";	
				$sql.="	ORDER BY   anio,mes";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
		
	}

	public function selec_total_proyectocomercial($searchQuery=null){
		$sql=" SELECT COUNT(DISTINCT prg_proyecto.project_id) AS total 
			FROM prg_proyecto left JOIN prg_proyecto_detalle on prg_proyecto.id_proyecto=prg_proyecto_detalle.id_proyecto
        WHERE prg_proyecto.flag='1' $searchQuery ";
		
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function update_detalleProyCom($id_proyecto,$coddetalle,$impuesto,$parent,$tipofactura,$montorestecnico,$igv,$montoampliacion,$montoreduccion,$codpuerto,$tonelada,$flgampliacion,$flgreduccion, $meso,$anioo,$noaplica,$is_igv,$is_curso,$analisisdsc,$observacion,$anio,$mes,$codestado,$codejecutivo,$montototal,$moneda,$is_analisis,$id_condicion, $is_viatico,$project_id_adm,$proyecto_adm,$id_proyecto_adm,$tipocambio,$codproducto,$usuario,$ip_modifica,$id_pais,$isanulado){
		 
		 $montoampliacion = !empty($montoampliacion) ? "'$montoampliacion'" : "NULL";
		 $montoreduccion = !empty($montoreduccion) ? "'$montoreduccion'" : "NULL";
		 $codpuerto = !empty($codpuerto) ? "'$codpuerto'" : "NULL";
		 $tonelada = !empty($tonelada) ? "'$tonelada'" : "NULL";
	   
		$sql="UPDATE prg_proyecto_detalle SET
				impuesto='$impuesto',
				parent='$parent',
				tipofactura='$tipofactura',
				montorestecnico='$montorestecnico',
				igv='$igv',
				montoampliacion=$montoampliacion,
				montoreduccion=$montoreduccion,
				codpuerto=$codpuerto,
				tonelada=$tonelada,
				flgampliacion='$flgampliacion',
				flgreduccion='$flgreduccion',
				meso='$meso',
				anioo='$anioo',
				noaplica='$noaplica',
				is_igv='$is_igv',
				is_curso='$is_curso',
				analisisdsc='$analisisdsc',
				observacion='$observacion', 	
				anio='$anio',
				mes='$mes',
				codestado='$codestado',
				codejecutivo='$codejecutivo',
				montototal='$montototal',
				moneda='$moneda',
				is_analisis='$is_analisis',
				id_condicion='$id_condicion',
				is_viatico='$is_viatico',
				isanulado='$isanulado',
				usuario_modifica='$usuario',
				ip_modifica='$ip_modifica',
				fecha_modifica=now(),
				project_id_adm='$project_id_adm',
				proyecto_adm='$proyecto_adm',
				id_proyecto_adm='$id_proyecto_adm',
				tipocambio='$tipocambio',
				codproducto='$codproducto'
		 WHERE id_proyecto=$id_proyecto and coddetalle=$coddetalle";
		 
		$consulta=$this->db->execute($sql);
        return $consulta;
	}
	
	public function insert_detalleProyCom($id_proyecto,$impuesto,$parent,$tipofactura,$montorestecnico,$igv,$montoampliacion,$montoreduccion,$codpuerto,$tonelada,$flgampliacion,$flgreduccion, $meso,$anioo,$noaplica,$is_igv,$is_curso,$analisisdsc,$observacion,$anio,$mes,$codestado,$codejecutivo,$montototal,$moneda,$is_analisis,$id_condicion, $is_viatico,$project_id_adm,$proyecto_adm,$id_proyecto_adm,$tipocambio,$codproducto,$usuario,$ip_modifica,$id_pais,$isanulado){
		 
		 $montoampliacion = !empty($montoampliacion) ? "'$montoampliacion'" : "NULL";
		 $montoreduccion = !empty($montoreduccion) ? "'$montoreduccion'" : "NULL";
		 $codpuerto = !empty($codpuerto) ? "'$codpuerto'" : "NULL";
		 $tonelada = !empty($tonelada) ? "'$tonelada'" : "NULL";
	   
		$sql="insert into prg_proyecto_detalle (montorestecnico,parent,
			flgampliacion,flgreduccion,impuesto,noaplica,
			montoampliacion,montoreduccion,
			codpuerto,tonelada,meso,anioo,tipofactura,igv,is_igv,is_curso,
            analisisdsc,observacion,anio,mes,codestado,codejecutivo,montototal,moneda,is_analisis,
			id_condicion,is_viatico,id_proyecto,usuario_ingreso,fecha_ingreso,ip_ingreso,
			project_id_adm,proyecto_adm,id_proyecto_adm,codproducto,tipocambio,isanulado) 
			values (
			'$montorestecnico','$parent','$flgampliacion','$flgreduccion','$impuesto','$noaplica', $montoampliacion,$montoreduccion,$codpuerto,$tonelada,'$meso','$anioo','$tipofactura','$igv','$is_igv','$is_curso',
			'$analisisdsc','$observacion',$anio,$mes,$codestado,$codejecutivo,$montototal,'$moneda','$is_analisis',
			'$id_condicion','$is_viatico',$id_proyecto,'$usuario',now(),'$ip_modifica',
			'$project_id_adm','$proyecto_adm','$id_proyecto_adm',
			'$codproducto','$tipocambio','$isanulado' )";
			// echo $sql;
		$consulta=$this->db->executeIns($sql);
        return $consulta;
	}
	
	public function selec_one_detalle_proyectoComercial($id_proyecto,$coddetalle,$id_pais){
		$sql="SELECT    parent,noaplica,igv, is_igv, is_curso,tipofactura, 
				id_proyecto_adm,
                anio,anioo,meso,mes,id_condicion,observacion,prg_proyecto_detalle.is_viatico,is_analisis,codestado,codejecutivo,
                montototal,moneda,analisisdsc, ifnull(prg_proyecto.ruc,'') as ruc,
				ifnull(tipocambio,'') as tipocambio,ifnull(codproducto,'') as codproducto,
				ifnull(codpuerto,'') as codpuerto,ifnull(tonelada,'') as tonelada ,
				ifnull(impuesto,0) as impuesto,
				ifnull(montoampliacion,'') as montoampliacion,
				ifnull(montoreduccion,'') as montoreduccion,
				ifnull(flgreduccion,'') as flgreduccion,
				ifnull(flgampliacion,'') as flgampliacion,
				ifnull(numcobranza,'') as numcobranza,
				ifnull(montorestecnico,'') as montorestecnico,
				ifnull(isanulado,'0') as isanulado
              FROM prg_proyecto INNER JOIN 
				prg_proyecto_detalle ON prg_proyecto.id_proyecto= prg_proyecto_detalle.id_proyecto
              WHERE prg_proyecto.flag = '1' AND prg_proyecto.id_pais = '$id_pais'
               and prg_proyecto.id_proyecto=$id_proyecto 
               and prg_proyecto_detalle.coddetalle=$coddetalle";
		$consulta=$this->db->consultarOne($sql);
		
        return $consulta;	
	}
	
	//**************************************************************
	// ACCIONES GENERADOS POR EL CRONOGRAMA PROYECTO
	
	// total generado en cronograma
	public function selec_total_cronogramabyProy($id_proyecto,$coddetalle){
		$sql="select sum(importe) as total
				from prg_cronogramapago 					
				where flag='1' and id_proyecto=$id_proyecto 					
				and coddetalle='$coddetalle'";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
	}
	
	public function update_cronogramaProy($id_cronograma,$id_proyecto,$coddetalle,$fecha,$importe,$moneda,$observacion,$fechanc,$montonc,$numeronc,$montoservicio,$nota,$montoncneto,$usuario,$ip,$id_pais){
		$sql="update prg_cronogramapago set 
				observacion='$observacion',
				fecha='$fecha',
				moneda='$moneda',
				importe=$importe,
				fechanc='$fechanc',
				montonc='$montonc',
				numeronc='$numeronc',
				montoservicio='$montoservicio',
				nota='$nota',
				montoncneto='$montoncneto',
				fecha_ingreso=now(),
				ip_ingreso='$ip',
				usuario_ingreso='$usuario'
		where id_cronograma=$id_cronograma and id_proyecto=$id_proyecto and coddetalle=$coddetalle";
		$consulta=$this->db->execute($sql);
        return $consulta;	
	}
	
	public function insert_cronogramaProy($id_proyecto,$coddetalle,$fecha,$importe,$moneda,$observacion,$fechanc,$montonc,$numeronc,$montoservicio,$nota,$montoncneto,$usuario,$ip,$id_pais){
		$sql="insert into prg_cronogramapago(coddetalle,moneda,importe,fecha,observacion,id_proyecto,fechanc,montonc,numeronc,montoservicio,nota,montoncneto,fecha_ingreso,ip_ingreso,usuario_ingreso) 
			values($coddetalle,'$moneda',$importe,'$fecha','$observacion',$id_proyecto,'$fechanc','$montonc','$numeronc','$montoservicio','$nota','$montoncneto',now(),'$ip','$usuario')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;	
	}
	
	public function regula_cronogramaProy($id_proyecto,$coddetalle){
		$sql=" update prg_proyecto_detalle  set flg_cronograma='1' where id_proyecto=$id_proyecto and coddetalle=$coddetalle";
		$consulta=$this->db->execute($sql);
        
		$sql=" UPDATE prg_cronogramapago c INNER JOIN prg_proyecto_detalle d ON d.coddetalle=c.coddetalle 
				SET c.tcambio=d.tipocambio		where c.coddetalle=$coddetalle";  
		$consulta=$this->db->execute($sql);
		
		return $consulta;	
	}
	
	public function delete_cronogramaProy($id_proyecto,$coddetalle,$id_cronograma){
		$sql=" UPDATE prg_cronogramapago set flag='0'
		   where coddetalle=$coddetalle and id_cronograma=$id_cronograma and id_proyecto=$id_proyecto";  
		$consulta=$this->db->execute($sql);
		
		return $consulta;	
	}
	
	//**************************************************************
	// ACCIONES GENERADOS POR EL COTOS PROYECTO
	
	public function update_costoProyCom($id_costo,$id_proyecto,$coddetalle,$programa,$preparacion,$auditoria,$reporte,$certificacion,$viaje,$moneda,$montofee,$montofeecert,$montocourier,$montoservicio,$montoviatico,$comentario,$ampliacion,$reduccion,$cartacor,$analisis,$cursos,$notacredito,$intercompany,$usuario,$ip,$id_pais,$auditoria_no_anunciada,$investigacion,$otros,$pm){
		
		$ampliacion = !empty($ampliacion) ? "'$ampliacion'" : "NULL";
		$reduccion = !empty($reduccion) ? "'$reduccion'" : "NULL";

		 
		$auditoria_no_anunciada = !empty($auditoria_no_anunciada) ? "'$auditoria_no_anunciada'" : "NULL";
		$investigacion = !empty($investigacion) ? "'$investigacion'" : "NULL";
		$otros = !empty($otros) ? "'$otros'" : "NULL";

		$sql="update prg_programacosto set 
			programa='$programa',
			preparacion='$preparacion',
			auditoria='$auditoria',
			reporte='$reporte',
			pm='$pm',
			certificacion='$certificacion',
			viaje='$viaje',
			moneda='$moneda',
			montofee='$montofee',
			montofeecert='$montofeecert',
			montocourier='$montocourier',
			montoservicio='$montoservicio',
			cartacor='$cartacor',
			analisis='$analisis',
			cursos='$cursos',
			notacredito='$notacredito',
			montoviatico='$montoviatico',
			ampliacion=$ampliacion,
			reduccion=$reduccion,
			intercompany='$intercompany',
			comentario='$comentario',
			fecha_ingreso=now(),
			ip_ingreso='$ip',
			usuario_ingreso='$usuario',
			auditoria_no_anunciada=$auditoria_no_anunciada,
			investigacion=$investigacion,
			otros=$otros
			where id_costo=$id_costo and project_id=$id_proyecto and coddetalle=$coddetalle";
	
		$consulta=$this->db->execute($sql);
        return $consulta;	
	}
	
	public function insert_costoProyCom($id_proyecto,$coddetalle,$programa,$preparacion,$auditoria,$reporte,$certificacion,$viaje,$moneda,$montofee,$montofeecert,$montocourier,$montoservicio,$montoviatico,$comentario,$ampliacion,$reduccion,$cartacor,$analisis,$cursos,$notacredito,$intercompany,$usuario,$ip,$id_pais,$auditoria_no_anunciada,$investigacion,$otros,$pm){
		
		 $ampliacion = !empty($ampliacion) ? "'$ampliacion'" : "NULL";
		 $reduccion = !empty($reduccion) ? "'$reduccion'" : "NULL";

		$auditoria_no_anunciada = !empty($auditoria_no_anunciada) ? "'$auditoria_no_anunciada'" : "NULL";
		$investigacion = !empty($investigacion) ? "'$investigacion'" : "NULL";
		$otros = !empty($otros) ? "'$otros'" : "NULL";
		 
		$sql="insert into prg_programacosto (pm,ampliacion,reduccion,montoviatico,coddetalle,programa,preparacion,auditoria,reporte,certificacion,viaje,moneda,
		montofee,montofeecert,montocourier,montoservicio,cartacor,analisis,cursos,notacredito,intercompany,comentario,project_id,fecha_ingreso,ip_ingreso,usuario_ingreso,
		auditoria_no_anunciada,investigacion,otros
		) 
		
		values('$pm',$ampliacion,$reduccion,'$montoviatico',$coddetalle,'$programa','$preparacion','$auditoria','$reporte','$certificacion','$viaje','$moneda',
		'$montofee','$montofeecert','$montocourier','$montoservicio','$cartacor','$analisis','$cursos','$notacredito','$intercompany','$comentario',$id_proyecto,now(),'$ip','$usuario',$auditoria_no_anunciada,$investigacion,$otros)";
	
		$consulta=$this->db->executeIns($sql);
        return $consulta;	
	}
	
	public function regula_costoProyCom($id_proyecto,$coddetalle){
		
		$sql=" UPDATE prg_proyecto_detalle INNER JOIN 
				( SELECT GROUP_CONCAT(DISTINCT programa)  AS programa,coddetalle 
				  FROM prg_programacosto WHERE flag='1' GROUP BY coddetalle) AS vista ON prg_proyecto_detalle.coddetalle=vista.coddetalle
				  SET  dsc_programaren= vista.programa
				  where prg_proyecto_detalle.coddetalle=$coddetalle";
		$consulta=$this->db->execute($sql);
       
		return $consulta;	
	}
	
	public function delete_costoProyCom($id_proyecto,$coddetalle,$id_costo){
		
		$sql=" UPDATE prg_programacosto 
				  SET  flag= '0'
				  where coddetalle=$coddetalle and id_costo=$id_costo";
		$consulta=$this->db->execute($sql);
       
		return $consulta;	
	}
	
	//********************************************************
	// programa costo
	//********************************************************
	public function selec_progDatosbyProy($coddetalle,$id_proyecto,$id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="select *
				from prg_programadatos 
				where flag='1' and project_id=$id_proyecto  and coddetalle=$coddetalle 
				order by programa  ";
				
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_one_progDatosbyProy($id_datos,$coddetalle,$id_proyecto,$id_pais){
		$sql="select *
				from prg_programadatos 
				where flag='1' and id_datos=$id_datos  and coddetalle=$coddetalle 
				order by programa  ";
				
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
	}
	
	public function update_programadatos($id_datos,$id_proyecto,$coddetalle,$programa,$productores,$idcalendario,$dias,$tipo,$auditorias,$usuario,$ip,$id_pais){
		
		 $productores = !empty($productores) ? "'$productores'" : "NULL";
		 $auditorias = !empty($auditorias) ? "'$auditorias'" : "NULL";

		$sql="update prg_programadatos set 
				programa='$programa',
				auditorias=$auditorias,
				productores=$productores,
				idcalendario='$idcalendario',
				tipo='$tipo',
				dias='$dias',
				fecha_ingreso=now(),
				ip_ingreso='$ip',
				usuario_ingreso='$usuario'
			where id_datos=$id_datos and project_id=$id_proyecto and coddetalle=$coddetalle";
		echo $sql;
		$consulta=$this->db->execute($sql);
        return $consulta;	
	}
	
	public function insert_programadatos($id_proyecto,$coddetalle,$programa,$productores,$idcalendario,$dias,$tipo,$auditorias,$usuario,$ip,$id_pais){
		
		 $productores = !empty($productores) ? "'$productores'" : "NULL";
		 $auditorias = !empty($auditorias) ? "'$auditorias'" : "NULL";
		
		 $sql="insert into 	prg_programadatos
				(idcalendario,coddetalle,dias,programa,auditorias,productores,tipo,project_id,fecha_ingreso,ip_ingreso,usuario_ingreso) 	
				values('$idcalendario',$coddetalle,'$dias','$programa',$auditorias,$productores,'$tipo',$id_proyecto,now(),'$ip','$usuario')";
			echo $sql;
		$consulta=$this->db->executeIns($sql);
        return $consulta;	
	}
	
	public function delete_programadatos($id_proyecto,$coddetalle,$id_datos,$usuario,$ip){
		
		$sql="update prg_programadatos set 
				flag='0',
				fecha_ingreso=now(),
				ip_ingreso='$ip',
				usuario_ingreso='$usuario'
			where id_datos=$id_datos and project_id=$id_proyecto and coddetalle=$coddetalle";
		
		$consulta=$this->db->execute($sql);
        return $consulta;	
	}
	
	//********************************************************
	// programa costo
	//********************************************************
	public function selec_auditProyecto($project_id,$id_pais){
		unset($this->listas);
		 $sql="select id, audit_id from prg_calendario 
               where id_pais='$id_pais' and id_proyecto='$project_id'  AND IFNULL(audit_id,'')!='' and flag='1'";
				
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	// FACTURACION OFISIS
	public function selec_factOfisis($porigv,$id_cronograma){
		unset($this->listas);
		$this->listas=[];
		$sql="select p.*, concat_ws('->',s.cod_desc,s.descripcion,s.id_subcuenta) as subcuenta,
				round(p.monto/(1 + $porigv),2) as montosin
			from ofi_detfactura p inner join ofi_subcuentas s on p.id_subcuenta=s.id_subcuenta
			where p.flag='1' and p.id_cronograma=$id_cronograma ";
				
		$consulta=$this->db->consultar($sql);		
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}	
        return $this->listas;	
	}
	
	public function selec_factOfisis_simple($id_cronograma){
		unset($this->listas);
		$sql="select * from ofi_detfactura 	where id_cronograma=$id_cronograma ";
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function update_ERP($id_producto,$id_subcuenta,$monto,$descripcion,$id_cronograma,$id_detalle,$codunegocio){
		$sql="update ofi_detfactura set
				id_producto=$id_producto,
				id_subcuenta=$id_subcuenta,
				codunegocio='$codunegocio',
				monto=$monto,
				descripcion='$descripcion'
			where id_cronograma=$id_cronograma and id_detalle=$id_detalle";
			
		$consulta=$this->db->execute($sql);
        return $consulta;	
	}
	
	public function update_cronolocalizacion($id_cronograma,$codlocalizacion,$serie){
		$sql="update prg_cronogramapago set 
				codlocalizacion='$codlocalizacion' ,
				serie='$serie' 
			where  id_cronograma=$id_cronograma ";
			echo $sql;
		$consulta=$this->db->execute($sql);
        return $consulta;	
	}
	
	
	public function insert_ERP($id_producto,$id_subcuenta,$monto,$descripcion,$id_cronograma,$codunegocio){
		$sql="insert into ofi_detfactura(id_producto,id_subcuenta,id_cronograma,monto,descripcion,codunegocio) 
			   values($id_producto,$id_subcuenta,$id_cronograma,$monto,'$descripcion','$codunegocio') ";
		echo $sql;	   
		$consulta=$this->db->executeIns($sql);
        return $consulta;	
	}
	
	public function selec_productoOfisis($id_pais){
		unset($this->listas);
		$sql="select p.id_producto,p.STMPDH_DESCRP
				from ofi_productos p 
                where p.STMPDH_TIPPRO in ('INCERT','INOTR') and p.flag='1' and id_pais='$id_pais' 
				order by 2  ";
				
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function selec_cuentaOfisis($id_pais,$txtcuenta=null){
		unset($this->listas);
		$sql="SELECT distinct id_subcuenta, concat_ws('->',cod_desc,descripcion) as cuenta
				from ofi_subcuentas 
				where flag='1' and id_pais='$id_pais'  ";
				
		if(!empty($txtcuenta))
			$sql.=" and (cod_desc  like '%$txtcuenta%' or descripcion like '%$txtcuenta') ";	
		
		$sql.="		order by 1";
				
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	
	
	
	public function selec_unegocioOfisis($id_pais){
		unset($this->listas);
		$this->listas=[];
		$sql="select codunegocio,unegocio
				from ofi_unegocio
				where flag='1' and id_pais='$id_pais' order by 2 ";
				
		$consulta=$this->db->consultar($sql);	
		if(!empty($consulta)){
			foreach($consulta as $filas){
				$this->listas[]=$filas;
			}
		}
        return $this->listas;	
	}
	
	public function selec_pre_senOfisis($porigv,$coddetalle,$id_proyecto,$id_cronograma){
		$sql="select 
			case when ifnull(prg_proyecto.ruc,'')='' then '' else trim(prg_proyecto.ruc) end as ruc, 
			case prg_cronogramapago.moneda when 'S/.' then 'PEN' when 'US$' then 'USD' else  prg_cronogramapago.moneda end as moneda,
			flg_ofisis,fecha_ofisis,usuario_ofisis,
			case prg_proyecto_detalle.is_igv when '1' then prg_cronogramapago.importe*$porigv else 0 end as igv,
			prg_cronogramapago.importe, DATE_FORMAT(prg_cronogramapago.fecha,'%d/%m/%y') as fecha_fe,
			DATE_FORMAT(prg_cronogramapago.fecha,'%Y-%m-%d') as fecha_ff,
			DATE_FORMAT(now(),'%d/%m/%Y') as fecha_f ,
			ifnull(ofi_vendedor,'V007') as codvendedor,
			prg_proyecto_detalle.tipofactura,
			ifnull(u.id_auditor,'0000') as codvendedor2,
			ifnull(is_igv,'1') as is_igv,
			ofi_localizacion.codigo as localizacion
		from prg_cronogramapago inner join 
			 prg_proyecto_detalle on prg_cronogramapago.coddetalle=prg_proyecto_detalle.coddetalle inner join 
			 prg_proyecto on prg_cronogramapago.id_proyecto=prg_proyecto.id_proyecto left join
			 prg_usuarios u on prg_proyecto_detalle.codejecutivo=u.id_usuario left join
			 ofi_localizacion on prg_cronogramapago.codlocalizacion= ofi_localizacion.codlocalizacion
		where prg_cronogramapago.flag='1' and prg_cronogramapago.coddetalle=$coddetalle  
			 and prg_cronogramapago.id_proyecto=$id_proyecto  and prg_cronogramapago.id_cronograma=$id_cronograma ";
		 
				
		$consulta=$this->db->consultarOne($sql);		
        return $consulta;	
	}
	
	public function selec_pre_senOfisisDetalle($porigv,$id_cronograma){
		$sql="select 
				p.*,STMPDH_TIPPRO, STMPDH_ARTCOD, 
				concat_ws('->',s.cod_desc,s.descripcion,s.id_subcuenta) as subcuenta,
				monto/(1+$porigv) as subtotal ,p.descripcion, s.cod_desc
			from ofi_detfactura p inner join 
				 ofi_subcuentas s on p.id_subcuenta=s.id_subcuenta inner join 
				 ofi_productos l on p.id_producto=l.id_producto
			where p.flag='1' and p.id_cronograma=$id_cronograma ";
				
		$consulta=$this->db->consultar($sql);	
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function selec_pre_senOfisisDetalleFtp($porigv,$id_cronograma){
		$sql="select 
			p.*,STMPDH_INDCOD as producto, 
			ofi_unegocio.codigo as unegocio, 
			monto/(1+$porigv) as subtotal ,
			(monto*$porigv)/(1+$porigv) as igv,
			p.descripcion, s.cod_desc
		from ofi_detfactura p inner join 
			 ofi_subcuentas s on p.id_subcuenta=s.id_subcuenta inner join 
			 ofi_productos l on p.id_producto=l.id_producto left join
			 ofi_unegocio on p.codunegocio=ofi_unegocio.codunegocio
		where p.flag='1' and p.id_cronograma=$id_cronograma ";
				
		$consulta=$this->db->consultar($sql);	
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function delete_itemfactura($id_detalle,$id_cronograma){
		$sql="update ofi_detfactura set flag='0'
				where id_detalle=$id_detalle and id_cronograma=$id_cronograma";
		$consulta=$this->db->execute($sql);
        return $consulta;	
	}
	
	public function update_senOfisis($id_cronograma,$usuario){
		$sql="update prg_cronogramapago 
			set fecha_ofisis=now(), usuario_ofisis='$usuario' ,flg_ofisis='1' 
			where id_cronograma=$id_cronograma ";
		$consulta=$this->db->execute($sql);
        return $consulta;	
	}
	
	public function delete_senOfisis($id_cronograma,$usuario_name){
		$sql="update prg_cronogramapago set flg_ofisis='0' where id_cronograma=$id_cronograma ";
		$consulta=$this->db->execute($sql);
        return $consulta;	
	}
	
	// crear ite de facturacion
	
	public function selectall_ofi_factura($id_cronograma){
		$sql="select * from ofi_detfactura where flag='1' and id_cronograma=$id_cronograma ";
				
		$consulta=$this->db->consultar($sql);	
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function selectall_ofi_detfactura($coddetalle,$id_pais){
		$sql="SELECT prg_programa.unidad, prg_programa.producto,ofi_productos.id_producto,ofi_unegocio.codunegocio,
				IFNULL(montoservicio,'') montoservicio, IFNULL(montoviatico,'') montoviatico,
				IFNULL(montofee,'') montofee, IFNULL(montofeecert,'') montofeecert  
				FROM prg_programacosto INNER JOIN prg_programa ON prg_programacosto.programa=prg_programa.iniciales
					INNER JOIN ofi_productos ON prg_programa.producto=ofi_productos.STMPDH_INDCOD
					INNER JOIN ofi_unegocio ON prg_programa.unidad=ofi_unegocio.codigo
				WHERE prg_programacosto.flag='1' AND coddetalle=$coddetalle  AND prg_programa.id_pais='$id_pais'";
				
		$consulta=$this->db->consultar($sql);	
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}
	
	public function execute_ofi_factura($sql){
		$consulta=$this->db->execute($sql);
        return $consulta;
	}


	public function select_reporte_proyectocosto($project_id){
		unset($this->listas);
			$sql="select * from prg_proyectocosto where proyecto='$project_id'";
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
        }
        return $this->listas;	
	}


	public function select_reporte_deuda($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$sql="SELECT *, date_format(fecha_f,'%d.%m.%y') as fecha,
				date_format(vencimiento_f,'%d.%m.%y') as vencimiento,
				date_format(recordatorio1_f,'%d.%m.%y') as recordatorio1,
				date_format(recordatorio2_f,'%d.%m.%y') as recordatorio2
			FROM prg_proyectocosto
			WHERE  1=1 $searchQuery ";
		$sql.=" order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage ;
	
		$consulta=$this->db->consultar($sql);		
		foreach($consulta as $filas){
            $this->listas[]=$filas;
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

	
	public function updateProgramaCosto($id_proyecto,$coddetalle,$moneda){
		 
		$sql="UPDATE prg_programacosto SET
				moneda='$moneda'
		 WHERE project_id=$id_proyecto and coddetalle=$coddetalle";
		 
		$consulta=$this->db->execute($sql);
		return $consulta;
	}
 
	public function updateCronogramaPago($id_proyecto,$coddetalle,$moneda){
		
		$sql="UPDATE prg_cronogramapago SET
				moneda='$moneda'
		WHERE id_proyecto=$id_proyecto and coddetalle=$coddetalle";
		
		$consulta=$this->db->execute($sql);
		return $consulta;
	}
 



}
?>