<?php 
//importando nuestras las referencias XAJAX
require ("xajax.common.php");


//funciÃ³n que lista las Particiones segun la IP elegida
function ListarOrigenMaster($ip){ 

	include_once("../includes/ctrlacc.php");
	include_once("../clases/AdoPhp.php");
	include_once("../includes/constantes.php");
	include_once("../includes/comunes.php");
	include_once("../includes/CreaComando.php");
	include_once("../includes/HTMLSELECT.php");
	
	
		//instanciamos el objeto para la respuesta AJAX
	 $objResponse = new xajaxResponse();	
	
	 $SelectHtml=" ";
	 $SelectHtml='<select name="source"> ';
	 
	 $cmd=CreaComando($cadenaconexion);
	 $rs=new Recordset; 
	 
	//Primera consulta: Particiones del MASTER potencialmente clonables.
    $cmd->texto='SELECT ordenadores_particiones.numpar as PART,nombresos.nombreso as OS 
	FROM ordenadores_particiones INNER JOIN tipospar ON tipospar.codpar=ordenadores_particiones.codpar
	INNER JOIN nombresos ON ordenadores_particiones.idnombreso=nombresos.idnombreso 	
	INNER JOIN ordenadores ON ordenadores_particiones.idordenador=ordenadores.idordenador 
	WHERE ordenadores.ip="' .$ip . '"   
	AND tipospar.clonable>0  
	AND ordenadores_particiones.idnombreso>0
	ORDER BY ordenadores_particiones.numpar';
		
	$rs->Comando=&$cmd; 
    	
  	if ($rs->Abrir()){
		$cantRegistros=$rs->numeroderegistros;
		if($cantRegistros>0){
			 $rs->Primero(); 
			while (!$rs->EOF){
				$SelectHtml.='<OPTION value=" 1 '.$rs->campos["PART"].'"';				
				$SelectHtml.='>';
				$SelectHtml.='PART: '. $rs->campos["OS"].'</OPTION>';
				$rs->Siguiente();
			}
		}
		else
		{			
		$objResponse->alert("No partion found in this host for use it to cloning other computers.");
		}
		$rs->Cerrar();
	}
	
	//Segunda consulta: Imagenes del MASTER registradas como si fuese un repo.
	$cmd->texto='SELECT *,repositorios.ip as iprepositorio FROM  imagenes
INNER JOIN repositorios ON repositorios.idrepositorio=imagenes.idrepositorio
where repositorios.ip="' .$ip .'"';
	
	$rs->Comando=&$cmd;
	
	if ($rs->Abrir()){
		$cantRegistros=$rs->numeroderegistros;
		if($cantRegistros>0){
			$rs->Primero(); 
			while (!$rs->EOF){
				$SelectHtml.='<OPTION value=" CACHE /'.$rs->campos["nombreca"].'"';				
				$SelectHtml.='>';
				$SelectHtml.='IMG-CACHE: ' . $rs->campos["nombreca"].'</OPTION>';
				$rs->Siguiente();
			}
		}
		else
		{			
		$objResponse->alert("No image found in CACHE in this host for use it to cloning other compuers.");
		}
		$rs->Cerrar();
	}
	
//Tercera consulta: Imagenes del REPO, que el MASTER se encargara de enivarlas
	$cmd->texto='SELECT *,repositorios.ip as iprepositorio FROM  imagenes
INNER JOIN repositorios ON repositorios.idrepositorio=imagenes.idrepositorio
where repositorios.idrepositorio=(select idrepositorio from ordenadores where ordenadores.ip="' .$ip .'")';
   
	
	$rs->Comando=&$cmd;
	
	if ($rs->Abrir()){
		$cantRegistros=$rs->numeroderegistros;
		if($cantRegistros>0){
			$rs->Primero(); 
			while (!$rs->EOF){
				$SelectHtml.='<OPTION value=" REPO /'.$rs->campos["nombreca"].'"';				
				$SelectHtml.='>';
				$SelectHtml.='IMG-REPO: ' . $rs->campos["nombreca"].'</OPTION>';
				$rs->Siguiente();
			}
		}
		else
		{			
		$objResponse->alert("No image found in REPO from this host for use it to cloning other computers.");
		}
		$rs->Cerrar();
	}
	
	$SelectHtml.= '</SELECT>';
	 
 
	 //asignando el contenido de la varabiale $SelectHTML al div que esta en la paquina inicial
	 $objResponse->assign("divListado","innerHTML",$SelectHtml);
	
	
	 return $objResponse; //retornamos la respuesta AJAX
}
	
$xajax->processRequest(); //procesando cualquier peticiÃ³n AJAX




?>