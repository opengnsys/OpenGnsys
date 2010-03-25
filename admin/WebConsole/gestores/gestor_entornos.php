<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_entornos.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de entornos
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$identorno=0; 

$ipserveradm="";
$portserveradm=0; 
$protoclonacion="";


if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros

if (isset($_GET["ipserveradm"])) $ipserveradm=$_GET["ipserveradm"]; 
if (isset($_GET["portserveradm"])) $portserveradm=$_GET["portserveradm"]; 
if (isset($_GET["protoclonacion"])) $protoclonacion=$_GET["protoclonacion"]; 

$tablanodo=""; // Arbol para nodos insertados
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
// *************************************************************************************************************************************************
?>
<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<BODY>
<?
	$literal="";
	switch($opcion){
		case $op_modificacion:
			$literal="resultado_modificar_entornos";
			break;
		default:
			break;
	}

if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo 'var oHTML'.chr(13);
	echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
	echo 'o=cTBODY.item(1);'.chr(13);
	if ($opcion==$op_alta )
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$identorno.",o.innerHTML);".chr(13);
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$identorno."');".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$identorno.")";
	echo '</SCRIPT>';
}

?>
</BODY>
</HTML>	
<?
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla entornos
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$identorno;
	global	$ipserveradm;
	global	$portserveradm;
	global	$protoclonacion;
	


	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$tablanodo;

	$cmd->CreaParametro("@identorno",$identorno,1);
	$cmd->CreaParametro("@ipserveradm",$ipserveradm,0);
	$cmd->CreaParametro("@portserveradm",$portserveradm,1);
	$cmd->CreaParametro("@protoclonacion",$protoclonacion,0);


	switch($opcion){
		case $op_modificacion:
			$cmd->texto="UPDATE entornos SET ipserveradm=@ipserveradm,portserveradm=@portserveradm,protoclonacion=@protoclonacion";
			$resul=$cmd->Ejecutar();
			break;

		default:
			break;
	}
	return($resul);
}
?>
