<?php 
//importando nuestras las referencias XAJAX
require ("xajax.common.php");



//función que lista las Particiones segun la IP elegida
function ListarParticionesXip($ip){ 
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../idiomas/php/".$idioma."/comandos/ejecutarscripts_".$idioma.".php");



$cmd=CreaComando($cadenaconexion);
define ("SQL_HOST", "localhost"); 
define("SQL_HOST_LOCAL", "localhost");
define ("SQL_USER", "usuog");
define ("SQL_PASS", "passusuog");
define ("DATABASE", "ogAdmBD");
$conexion=mysql_connect(SQL_HOST, SQL_USER, SQL_PASS) or die ('no se ha podido conectar con mysql');
mysql_select_db(DATABASE, $conexion);

	
	
	$objResponse = new xajaxResponse();
	//instanciamos el objeto para la respuesta AJAX
	//$objResponse->alert("Este equipo tiene ".$cantRegistros." particiones clonables.");
	//ISO-8859-1 significa que los caracteres latinos como la ñ y los acentos seran tomados en cuenta
	//$sql=sprintf('select xxxxxx where id=%d',$ip);
	//$sql='SELECT ordenadores_particiones.numpar,nombresos.nombreso FROM ordenadores_particiones INNER JOIN tipospar ON tipospar.codpar=ordenadores_particiones.codpar INNER JOIN nombresos ON ordenadores_particiones.idnombreso=nombresos.idnombreso 	WHERE ordenadores_particiones.idordenador=' .$ip . '  AND tipospar.clonable>0  AND ordenadores_particiones.idnombreso>0 ORDER BY ordenadores_particiones.numpar';
	
        $sql='SELECT ordenadores_particiones.numpar,nombresos.nombreso 
	FROM ordenadores_particiones INNER JOIN tipospar ON tipospar.codpar=ordenadores_particiones.codpar
	INNER JOIN nombresos ON ordenadores_particiones.idnombreso=nombresos.idnombreso 	
	INNER JOIN ordenadores ON ordenadores_particiones.idordenador=ordenadores.idordenador 
	WHERE ordenadores.ip="' .$ip . '"   
	AND tipospar.clonable>0  
	AND ordenadores_particiones.idnombreso>0
	ORDER BY ordenadores_particiones.numpar';
       //$objResponse->alert("Consulta ".$sql." .");	
       
       
	$element=" ";
	//$rs=new Recordset; 
	//$rs->Comando=&$cmd; 
             

       
       
       
       //$element='<select name="PartOrigen"> <option value="">--Particion a Enviar--</option>';
        //($rs->Abrir()){
	//	$rs->Primero(); 
	//	while (!$rs->EOF){
	//		$element.='<OPTION value="'.$rs->campos["numpart"] . '" ';
	//		$element.='>';
	//		$element.= $rs->campos["numpar"] .'</OPTION>';
	//		$rs->Siguiente();
	//	}
	//	$rs->Cerrar();
	//	$element.='</select>';
	//}
       
       
       $rsParticiones=mysql_query($sql);
$cantRegistros=mysql_num_rows($rsParticiones);
	
	$element=''; //variable donde guardaremos el elemento del formulario que luego se mostrara mediante AJAX
	//
	if($cantRegistros>0){ // Si existen registros entonces armamos la cabecera de los elementos del formulario
		$element='<select name="PartOrigen"> <option value="">--Particion a Enviar--</option>';
		while($row=mysql_fetch_array($rsParticiones)){ //recorriendo registro x registro y armando la variable element
		 $element.='  <option value=' .$row[0].   ' > '  .$row[0]. ' - '.$row[1]. '</OPTION> ';
		}
		$element.='</select>';
	 }
	 //asignando el contenido de la varabiale $element al div que esta en la paquina inicial
	 //innerHTML reemplaza el contenido HTML por otro
	 $objResponse->assign("divListado","innerHTML",$element);
	 //mostramos un alert
//$objResponse->alert("Este equipo tiene ".$cantRegistros." particiones clonables.");
	 return $objResponse; //retornamos la respuesta AJAX
}
	
$xajax->processRequest(); //procesando cualquier petición AJAX
?>