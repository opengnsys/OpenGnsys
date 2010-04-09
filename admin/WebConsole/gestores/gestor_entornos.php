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
$repositorio="";

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros

if (isset($_POST["ipserveradm"])) $ipserveradm=$_POST["ipserveradm"]; 
if (isset($_POST["portserveradm"])) $portserveradm=$_POST["portserveradm"]; 
if (isset($_POST["protoclonacion"])) $protoclonacion=$_POST["protoclonacion"]; 
if (isset($_POST["repositorio"])) $repositorio=$_POST["repositorio"]; 

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
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
<SCRIPT language="javascript">
<?
	if ($resul)
		echo "alert(CTbMsg[5]);";
	else
		echo "alert(CTbMsg[8]);";
?>
history.back();
</SCRIPT> 
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
	global	$repositorio;


	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$tablanodo;

	$cmd->CreaParametro("@identorno",$identorno,1);
	$cmd->CreaParametro("@ipserveradm",$ipserveradm,0);
	$cmd->CreaParametro("@portserveradm",$portserveradm,1);
	$cmd->CreaParametro("@protoclonacion",$protoclonacion,0);
	$cmd->CreaParametro("@repositorio",$repositorio,1);

	switch($opcion){
		case $op_modificacion:
			$cmd->texto="UPDATE entornos SET ipserveradm=@ipserveradm,portserveradm=@portserveradm,protoclonacion=@protoclonacion,repositorio=@repositorio";
			$resul=$cmd->Ejecutar();
			break;

		default:
			break;
	}
	return($resul);
}
?>
