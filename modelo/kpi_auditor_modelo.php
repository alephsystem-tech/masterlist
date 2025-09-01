<?php
class kpi_auditor_model{
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

	public function select_kpiauditor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT * from kpi_auditor where flag='1'  $searchQuery ";
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
	public function selec_total_kpiauditor($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM 
            kpi_auditor
        WHERE flag='1' $searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	public function select_oldauditor($searchQuery,$columnName,$columnSortOrder, $row,$rowperpage){
		unset($this->listas);
		$this->listas=[];
		$sql="SELECT DISTINCT  a.id_auditor, concat_ws(' ',a.nombre,a.apepaterno,a.apematerno) as auditor , 
				p.nombre as pais, prg_usuarios.usuario
			from prg_auditor a inner join 
				 prg_paises p on a.id_pais=p.id_pais inner join
				 prg_usuarios on a.id_auditor=prg_usuarios.id_auditor
			WHERE prg_usuarios.flag='1' and a.flag='1'  
				and a.id_auditor not in (select DISTINCT IFNULL(ref_auditor,0) from kpi_auditor where flag='1' )
				$searchQuery ";
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
	public function selec_total_oldauditor($searchQuery=null){
		$sql=" SELECT COUNT(*) AS total 
			FROM prg_auditor a inner join 
				 prg_paises p on a.id_pais=p.id_pais inner join
				 prg_usuarios on a.id_auditor=prg_usuarios.id_auditor
			WHERE prg_usuarios.flag='1' and a.flag='1'  
				and a.id_auditor not in (select DISTINCT IFNULL(ref_auditor,0) from kpi_auditor where flag='1' )
				$searchQuery " ;
		$consulta=$this->db->consultarOne($sql);
		return $consulta;	
	}
	
	
	public function selec_one_kpiauditor($codauditor){
		
		$sql="SELECT *
				from kpi_auditor where codauditor=$codauditor ";
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	 public function insert_kpiauditor($nombres,$codigo,$email,$ref_pais,$tipokpi,$id_pais,$usuario,$ip){

        $sql="insert into kpi_auditor(nombres,codigo,email,ref_pais,id_pais,tipokpi, flag,usuario_ingreso,fecha_ingreso,ip_ingreso,usuario_modifica,fecha_modifica,ip_modifica)
        values('$nombres','$codigo','$email','$ref_pais', '$id_pais','$tipokpi','1','$usuario',now(),'$ip', '$usuario',now(),'$ip')";
			
		$consulta=$this->db->executeIns($sql);
        return $consulta;
    }	

	// update usuario
    public function update_kpiauditor($codauditor,$nombres,$codigo,$email,$ref_pais,$tipokpi,$id_pais,$usuario,$ip){
	   
        $sql="update kpi_auditor 
				set nombres='$nombres',codigo='$codigo',
				email='$email',ref_pais='$ref_pais',tipokpi='$tipokpi',
				usuario_modifica='$usuario',fecha_modifica=now(),ip_modifica='$ip'
                where codauditor=$codauditor and id_pais='$id_pais'";
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	

    public function delete_kpiauditor($codauditor){
	   
        $sql="update kpi_auditor set flag='0' where codauditor=$codauditor";
		
		$consulta=$this->db->execute($sql);
        return $consulta;
    }	


	public function validate_kpiauditor($codigo,$email,$codauditor){
		
		
	$sql="SELECT usuario FROM prg_usuarios WHERE flag='1' and usuario='$codigo'
			union
			select nom_usuario from prg_auditor where email='$email' and flag='1'
			UNION
			SELECT codigo FROM kpi_auditor WHERE flag='1' and (codigo='$codigo' or email='$email')";
	  if(!empty($_POST['codauditor'])){
		  $codauditor=$_POST['codauditor'];
		  $sql.=" and codauditor!=$codauditor";
	  }  
	
		$consulta=$this->db->consultarOne($sql);
        return $consulta;	
		
	}
	
	
}
?>