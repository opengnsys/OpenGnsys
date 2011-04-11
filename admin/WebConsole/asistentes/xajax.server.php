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
	 $cmd=CreaComando($cadenaconexion);

    $cmd->texto='SELECT ordenadores_particiones.numpar as PART,nombresos.nombreso as OS 
	FROM ordenadores_particiones INNER JOIN tipospar ON tipospar.codpar=ordenadores_particiones.codpar
	INNER JOIN nombresos ON ordenadores_particiones.idnombreso=nombresos.idnombreso 	
	INNER JOIN ordenadores ON ordenadores_particiones.idordenador=ordenadores.idordenador 
	WHERE ordenadores.ip="' .$ip . '"   
	AND tipospar.clonable>0  
	AND ordenadores_particiones.idnombreso>0
	ORDER BY ordenadores_particiones.numpar';
	
	$rs=new Recordset; 	
	$rs->Comando=&$cmd; 
       
      
	
  	if ($rs->Abrir()){
		$cantRegistros=$rs->numeroderegistros;
		if($cantRegistros>0){
			 $SelectHtml='<select name="PartOrigen"> <option value="">--Particion a Enviar--</option>';
			$rs->Primero(); 
			while (!$rs->EOF){
				$SelectHtml.='<OPTION value="'.$rs->campos["PART"];
				$SelectHtml.='>';
				$SelectHtml.= $rs->campos["OS"].'</OPTION>';
				$rs->Siguiente();
			}
		}
		else
		{
		$objResponse->alert("Este equipo No tiene particiones clonables.");
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