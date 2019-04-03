<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Noviembre-2005
// Nombre del fichero: toma_mes.php
// Descripción :
//		Crea la tabla del mes y el año elegidos
// *************************************************************************************************************************************************

include_once("../includes/ctrlacc.php");
include_once("../idiomas/php/".$idioma."/clases/Almanaque_".$idioma.".php");

// Toma parametros
$pidmes=0;
$pidanno=0;
$pvitem=0;

if (isset($_POST["idmes"]))	$pidmes=$_POST["idmes"];
if (isset($_POST["idanno"])) $pidanno=$_POST["idanno"];

$mialmanaque= new Almanaque("tabla_meses");
$retorno=$mialmanaque->MesAnno($pidmes,$pidanno);
echo $retorno;
?>