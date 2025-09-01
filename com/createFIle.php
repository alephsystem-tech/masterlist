<?php
if(!file_exists($filerutaSol.$file)){
    $plantilla="../plantilla/formato-tablas.php";
    $gestor=fopen($plantilla, "r");
    $contenido = fread($gestor, filesize($plantilla));
	$contenido =str_replace('##LOGO##',$STRING_LOGO,$contenido);
    $contenido =str_replace('##PROYECTO##',$proyecto_res['proyecto'],$contenido);
	$contenido =str_replace('##CONTACTO##',$proyecto_res['contacto'],$contenido);
	$contenido =str_replace('##EMAIL##',$proyecto_res['email'],$contenido);
	
	$contenido =str_replace('##FECHA##',$solicitud_res['fecha_f'],$contenido);
    $contenido =str_replace('##CODIGO##',$solicitud_res['project_id'],$contenido);
	$contenido =str_replace('##CODIGO##',$solicitud_res['project_id'],$contenido);
	$contenido =str_replace('##NUMERO_MOSTRAR##',$solicitud_res['numero_mostrar'],$contenido);
	$contenido =str_replace('##BLOQUEDETALLE##',$tabla,$contenido);

    fclose($gestor);

    $mpdf=new mPDF('R','A4', 12,'Arial');
    $mpdf->WriteHTML($contenido);
    $mpdf->Output($filerutaSol.$file,'F');
}   
?>