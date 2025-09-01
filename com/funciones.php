<?php

function f_limpiarcaracter($strText) {
	//returns safe code for preloading in the RTE
	$tmpString = trim($strText);
	$tmpString = str_replace("'", "", $tmpString);

	$tmpString = str_replace('°', "", $tmpString);

	$tmpString = str_replace("Ñ","&Ntilde;", $tmpString);
	$tmpString = str_replace("ñ","n", $tmpString);
	$tmpString = str_replace("á","a", $tmpString);
	$tmpString = str_replace("é","e", $tmpString);
	$tmpString = str_replace("í","í", $tmpString);
	$tmpString = str_replace("ó","o", $tmpString);
	$tmpString = str_replace("ú","u", $tmpString);
	$tmpString = str_replace("Á","A", $tmpString);
	$tmpString = str_replace("É","E", $tmpString);
	$tmpString = str_replace("Í","I", $tmpString);
	$tmpString = str_replace("Ó","O", $tmpString);
	$tmpString = str_replace("Ú","U", $tmpString);
	
	
	return $tmpString;
}

function uploadFile($_ARCH,$rutbase,$control){
	// ulpload file
	//*********************************************************************************
	
	$valid_extensions = array('xls','doc','docx','xlsx','pdf','ppt','pptx','jpg','png','gif','jpeg','rar','zip'); // valid extensions

	
	if(!empty($_ARCH[$control]['name']) ){
		$nombrefile = $_ARCH[$control]['name'];
		$tmp = $_ARCH[$control]['tmp_name'];
		$nombrefile = strtolower(caracterquitaFile($nombrefile));
		
				  
		// get uploaded file's extension
		$ext = pathinfo($nombrefile, PATHINFO_EXTENSION);
		// can upload same image using rand function
		// check's valid format
			   
		if(in_array($ext, $valid_extensions)){					
			$path = $rutbase.$nombrefile;	
			if(move_uploaded_file($tmp,'../'.$path)){
				 return  $nombrefile;
				 // echo $rutbase;
			} else
				return "Error.No se puede cargar archivo.";
		} else
			return "Error.Extension no valida.";
	}else
		return "Error. Not file";
}
	
	
function formatdatedos($date){
    // va venir con el formato dd/mm/aaaa
	// change to aaaa/mm/dd
   $adate=explode("/",$date);
   $fechaok="$adate[2]/$adate[1]/$adate[0]";
   return $fechaok;
}

	
function formatdateCal($date){
    // va venir con el formato aaaa-mm-dd
	// change to dd/mm/yyyy
	$fechaok="";

	if(!empty($date)){
		$adate=explode("-",$date);
		$fechaok="$adate[2]/$adate[1]/$adate[0]";
	}
   return $fechaok;
}

function char_potencia($strText){
    $tmpString = trim($strText);
    if(strpos($tmpString,'^')){
        $posini=strpos($tmpString,'^');
        $posfin=$posini+2;
        $cadei=substr($tmpString,0,$posfin);
        $cadei = str_replace("^","<sup>", $cadei);
        $cadef=substr($tmpString,$posfin);
        $tmpString = $cadei. "</sup>".$cadef;
    }
    return $tmpString;
    
}

function f_caracterrestaura($strText) {
	//returns safe code for preloading in the RTE
	$tmpString = trim($strText);

	//convert all types of single quotes
	$tmpString = str_replace("&#39;","'", $tmpString);
	$tmpString = str_replace("&#34;",'"', $tmpString);
	$tmpString = str_replace("&#176;",'°', $tmpString);
	return $tmpString;
}

function f_limpiar_save($strText) {
	//returns safe code for preloading in the RTE
	$tmpString = trim($strText);

	//convert all types of single quotes
	$tmpString = str_replace("'","", $tmpString);
	$tmpString = str_replace('"',"", $tmpString);
	$tmpString = str_replace('°',"", $tmpString);
	$tmpString = str_replace('”',"", $tmpString);
	$tmpString = str_replace('“',"", $tmpString);
	 
	return $tmpString;
}


function caracterBad($strText) {
	//returns safe code for preloading in the RTE
	$tmpString = trim($strText);

	//convert all types of single quotes
	$tmpString = str_replace("'","", $tmpString);
	$tmpString = str_replace("´","", $tmpString);
	$tmpString = str_replace("Â","A", $tmpString);
	$tmpString = str_replace("Ã","A", $tmpString);
	$tmpString = str_replace("´","", $tmpString);
	$tmpString = str_replace("´","", $tmpString);
	$tmpString = str_replace("á;","a", $tmpString);
	$tmpString = str_replace("é","e", $tmpString);
	$tmpString = str_replace("í","i", $tmpString);
	$tmpString = str_replace("ó","o", $tmpString);
	$tmpString = str_replace("ú","u", $tmpString);
	$tmpString = str_replace("Á","A", $tmpString);
	$tmpString = str_replace("É","E", $tmpString);
	$tmpString = str_replace("Í;","I", $tmpString);
	$tmpString = str_replace("Ó","O", $tmpString);
	$tmpString = str_replace("Ú","U", $tmpString);
	$tmpString = str_replace("Ñ","N", $tmpString);
	$tmpString = str_replace("ñ","n", $tmpString);
	$tmpString = str_replace(" ","_", $tmpString);
	$tmpString = str_replace("[","_", $tmpString);
	$tmpString = str_replace("]","_", $tmpString);
	$tmpString = str_replace("'","", $tmpString);
	$tmpString = str_replace("´","", $tmpString);
	
	return $tmpString;
}


function caracterquitaFile($strText) {
	//returns safe code for preloading in the RTE
	$tmpString = trim($strText);

	//convert all types of single quotes
	$tmpString = str_replace("á;","a", $tmpString);
	$tmpString = str_replace("é","e", $tmpString);
	$tmpString = str_replace("í","i", $tmpString);
	$tmpString = str_replace("ó","o", $tmpString);
	$tmpString = str_replace("ú","u", $tmpString);
	$tmpString = str_replace("Á","A", $tmpString);
	$tmpString = str_replace("É","E", $tmpString);
	$tmpString = str_replace("Í;","I", $tmpString);
	$tmpString = str_replace("Ó","O", $tmpString);
	$tmpString = str_replace("Ú","U", $tmpString);
	$tmpString = str_replace("Ñ","N", $tmpString);
	$tmpString = str_replace("ñ","n", $tmpString);
	$tmpString = str_replace(" ","_", $tmpString);
	$tmpString = str_replace("[","_", $tmpString);
	$tmpString = str_replace("]","_", $tmpString);
	$tmpString = str_replace("'","", $tmpString);
	$tmpString = str_replace("´","", $tmpString);
	
	return $tmpString;
}


function caracterquita($strText) {
	//returns safe code for preloading in the RTE
	$tmpString = trim($strText);

	//convert all types of single quotes
	$tmpString = str_replace("á;","a", $tmpString);
	$tmpString = str_replace("é","e", $tmpString);
	$tmpString = str_replace("í","i", $tmpString);
	$tmpString = str_replace("ó","o", $tmpString);
	$tmpString = str_replace("ú","u", $tmpString);
	$tmpString = str_replace("Á","A", $tmpString);
	$tmpString = str_replace("É","E", $tmpString);
	$tmpString = str_replace("Í;","I", $tmpString);
	$tmpString = str_replace("Ó","O", $tmpString);
	$tmpString = str_replace("Ú","U", $tmpString);
	$tmpString = str_replace("Ñ","N", $tmpString);
	$tmpString = str_replace("ñ","n", $tmpString);
	
	return $tmpString;
}


function caracterlimpia($strText) {
	//returns safe code for preloading in the RTE
	$tmpString = trim($strText);

	//convert all types of single quotes
	$tmpString = str_replace("&Ntilde;","Ñ", $tmpString);
	$tmpString = str_replace("&ntilde;","ñ", $tmpString);
	$tmpString = str_replace("&aacute;","á", $tmpString);
	$tmpString = str_replace("&eacute;","é", $tmpString);
	$tmpString = str_replace("&iacute;","í", $tmpString);
	$tmpString = str_replace("&oacute;","ó", $tmpString);
	$tmpString = str_replace("&uacute;","ú", $tmpString);
	$tmpString = str_replace("&Aacute;","Á", $tmpString);
	$tmpString = str_replace("&Eacute;","É", $tmpString);
	$tmpString = str_replace("&Iacute;","Í", $tmpString);
	$tmpString = str_replace("&Oacute;","Ó", $tmpString);
	$tmpString = str_replace("&Uacute;","Ú", $tmpString);
	return $tmpString;
}

function caracterlimpia_alt($strText) {
	//returns safe code for preloading in the RTE
	$tmpString = trim($strText);

	//convert all types of single quotes
	$tmpString = str_replace("&Ntilde;","N", $tmpString);
	$tmpString = str_replace("&ntilde;","n", $tmpString);
	$tmpString = str_replace("&aacute;","a", $tmpString);
	$tmpString = str_replace("&eacute;","e", $tmpString);
	$tmpString = str_replace("&iacute;","i", $tmpString);
	$tmpString = str_replace("&oacute;","o", $tmpString);
	$tmpString = str_replace("&uacute;","u", $tmpString);
	$tmpString = str_replace("&Aacute;","A", $tmpString);
	$tmpString = str_replace("&Eacute;","E", $tmpString);
	$tmpString = str_replace("&Iacute;","I", $tmpString);
	$tmpString = str_replace("&Oacute;","O", $tmpString);
	$tmpString = str_replace("&Uacute;","U", $tmpString);
   $tmpString = str_replace("&#39", ";", $tmpString);
	$tmpString = str_replace('&#34;', "", $tmpString);
	$tmpString = str_replace('&#176;', "", $tmpString);
   $tmpString = str_replace("&amp;", "&", $tmpString);
   $tmpString = str_replace("&#039;", "'", $tmpString);
   
        
	return $tmpString;
}

function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
       } 
     } 
     reset($objects); 
     rmdir($dir); 
   } 
} 


function num2letras($num, $fem = true, $dec = true) {
   $matuni[2]  = "dos";
   $matuni[3]  = "tres";
   $matuni[4]  = "cuatro";
   $matuni[5]  = "cinco";
   $matuni[6]  = "seis";
   $matuni[7]  = "siete";
   $matuni[8]  = "ocho";
   $matuni[9]  = "nueve";
   $matuni[10] = "diez";
   $matuni[11] = "once";
   $matuni[12] = "doce";
   $matuni[13] = "trece";
   $matuni[14] = "catorce";
   $matuni[15] = "quince";
   $matuni[16] = "dieciseis";
   $matuni[17] = "diecisiete";
   $matuni[18] = "dieciocho";
   $matuni[19] = "diecinueve";
   $matuni[20] = "veinte";
   $matunisub[2] = "dos";
   $matunisub[3] = "tres";
   $matunisub[4] = "cuatro";
   $matunisub[5] = "quin";
   $matunisub[6] = "seis";
   $matunisub[7] = "sete";
   $matunisub[8] = "ocho";
   $matunisub[9] = "nove";

   $matdec[2] = "veint";
   $matdec[3] = "treinta";
   $matdec[4] = "cuarenta";
   $matdec[5] = "cincuenta";
   $matdec[6] = "sesenta";
   $matdec[7] = "setenta";
   $matdec[8] = "ochenta";
   $matdec[9] = "noventa";
   $matsub[3]  = 'mill';
   $matsub[5]  = 'bill';
   $matsub[7]  = 'mill';
   $matsub[9]  = 'trill';
   $matsub[11] = 'mill';
   $matsub[13] = 'bill';
   $matsub[15] = 'mill';
   $matmil[4]  = 'millones';
   $matmil[6]  = 'billones';
   $matmil[7]  = 'de billones';
   $matmil[8]  = 'millones de billones';
   $matmil[10] = 'trillones';
   $matmil[11] = 'de trillones';
   $matmil[12] = 'millones de trillones';
   $matmil[13] = 'de trillones';
   $matmil[14] = 'billones de trillones';
   $matmil[15] = 'de billones de trillones';
   $matmil[16] = 'millones de billones de trillones';

   $num=round($num,2);
   $num = trim((string)@$num);
   if ($num[0] == '-') {
      $neg = 'menos ';
      $num = substr($num, 1);
   }else
      $neg = '';
   while ($num[0] == '0') $num = substr($num, 1);
   if ($num[0] < '1' or $num[0] > 9) $num = '0' . $num;
   $zeros = true;
   $punt = false;
   $ent = '';
   $fra = '';
   for ($c = 0; $c < strlen($num); $c++) {
      $n = $num[$c];
      if (! (strpos(".,'''", $n) === false)) {
         if ($punt) break;
         else{
            $punt = true;
            continue;
         }

      }elseif (! (strpos('0123456789', $n) === false)) {
         if ($punt) {
            if ($n != '0') $zeros = false;
            $fra .= $n;
         }else

            $ent .= $n;
      }else

         break;

   }
   $ent = '     ' . $ent;
   if ($dec and $fra and ! $zeros) {
      if(strlen($fra)==1) $fin = ' con '.$fra.'0/100';
	  else $fin = ' con '.$fra.'/100';
   }else
      $fin = ' y 00/100';
   if ((int)$ent === 0) return 'Cero ' . $fin;
   $tex = '';
   $sub = 0;
   $mils = 0;
   $neutro = false;
   while ( ($num = substr($ent, -3)) != '   ') {
      $ent = substr($ent, 0, -3);
      if (++$sub < 3 and $fem) {
         $matuni[1] = 'una';
         $subcent = 'as';
      }else{
         $matuni[1] = $neutro ? 'un' : 'uno';
         $subcent = 'os';
      }
      $t = '';
      $n2 = substr($num, 1);
      if ($n2 == '00') {
      }elseif ($n2 < 21)
         $t = ' ' . $matuni[(int)$n2];
      elseif ($n2 < 30) {
         $n3 = $num[2];
         if ($n3 != 0) $t = 'i' . $matuni[$n3];
         $n2 = $num[1];
         $t = ' ' . $matdec[$n2] . $t;
      }else{
         $n3 = $num[2];
         if ($n3 != 0) $t = ' y ' . $matuni[$n3];
         $n2 = $num[1];
         $t = ' ' . $matdec[$n2] . $t;
      }
      $n = $num[0];
      if ($n == 1) {
         $t = ' ciento' . $t;
      }elseif ($n == 5){
         $t = ' ' . $matunisub[$n] . 'ient' . $subcent . $t;
      }elseif ($n != 0){
         $t = ' ' . $matunisub[$n] . 'cient' . $subcent . $t;
      }
      if ($sub == 1) {
      }elseif (! isset($matsub[$sub])) {
         if ($num == 1) {
            $t = ' mil';
         }elseif ($num > 1){
            $t .= ' mil';
         }
      }elseif ($num == 1) {
         $t .= ' ' . $matsub[$sub] . 'on';
      }elseif ($num > 1){
         $t .= ' ' . $matsub[$sub] . 'ones';
      }
      if ($num == '000') $mils ++;
      elseif ($mils != 0) {
         if (isset($matmil[$sub])) $t .= ' ' . $matmil[$sub];
         $mils = 0;
      }
      $neutro = true;
      $tex = $t . $tex;
   }
   $tex = $neg . substr($tex, 1) . $fin;
   return ucfirst($tex);
}
  

function zfill($numero, $longitud){
  $numfin='';
  $zeros="";
  if(strlen($numero)<=$longitud){
      $l_falta=$longitud - strlen($numero);
	  for($i=0;$i<$l_falta;$i++){
	    $zeros.='0';
	  }
	  $numfin = $zeros.$numero;
  }else{
     $numfin=$numero;
  }
  return   $numfin;
}

function quemesdesemana($semana){
	$mes=1;
	if($semana>=48)
		$mes=12;
	else if($semana>=44)
		$mes=11;
	else if($semana>=40)
		$mes=10;
	else if($semana>=36)
		$mes=9;
	else if($semana>=32)
		$mes=8;
	else if($semana>=27)
		$mes=7;
	else if($semana>=23)
		$mes=6;
	else if($semana>=18)
		$mes=5;
	else if($semana>=14)
		$mes=4;
	else if($semana>=10)
		$mes=3;
	else if($semana>=6)
		$mes=2;
	else if($semana>=1)
		$mes=1;	
		
  return   $mes;
}


$queMes=array("","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Set","Oct","Nov","Dic");
$queMesFull=array("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Setiembre","Octubre","Noviembre","Diciembre");


function nameDiaReal($dia){
   $name='';
   switch($dia){
     case 7: $name= 'Domingo'; break;
     case 1: $name= 'Lunes'; break;
     case 2: $name= 'Martes'; break;
     case 3: $name= 'Miercoles'; break;
     case 4: $name= 'Jueves'; break;
     case 5: $name= 'Viernes'; break;
     case 6: $name= 'Sabado'; break;
   }
   return $name;
}

function namemes($mes){
   $name='';
   switch($mes){
     case 1: $name= 'Enero'; break;
     case 2: $name= 'Febrero'; break;
     case 3: $name= 'Marzo'; break;
     case 4: $name= 'Abril'; break;
     case 5: $name= 'Mayo'; break;
     case 6: $name= 'Junio'; break;
     case 7: $name= 'Julio'; break;
     case 8: $name= 'Agosto'; break;
     case 9: $name= 'Setiembre'; break;
	 case 10: $name= 'Octubre'; break;
     case 11: $name= 'Noviembre'; break;
     case 12: $name= 'Diciembre'; break;
   }
   return $name;
}

function convertDate($dateValue) {    
  $unixDate = (intval($dateValue) - 25569) * 86400;
  return gmdate("Y/m/d", $unixDate);
 
}
?>