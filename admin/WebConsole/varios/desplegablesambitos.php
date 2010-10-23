<?
//*******************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: desplegablesambitos.php
// Descripción : 
//		 Devuelve el desplegable de un ámbito ( Usado sólo por AJAX)
// *******************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/CreaComando.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/comunes.php");
include_once("../includes/constantes.php");
include_once("../includes/HTMLSELECT.php");
//________________________________________________________________________________________________________

$ambito=0; 

if (isset($_POST["ambito"])) $ambito=$_POST["ambito"];  // Recoge parametros

//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if ($cmd){	
	echo tomaSelectAmbito($cmd,$ambito,0,$idcentro,250);
}
?>