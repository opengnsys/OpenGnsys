<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_entornos.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de entornos
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________

$identorno=0; 

$ipserveradm="";
$portserveradm=0; 
$protoclonacion="";


if (isset($_POST["ipserveradm"])) $ipserveradm=$_POST["ipserveradm"]; 
if (isset($_POST["portserveradm"])) $portserveradm=$_POST["portserveradm"]; 
if (isset($_POST["protoclonacion"])) $protoclonacion=$_POST["protoclonacion"]; 

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
	<SCRIPT language="javascript" src="../jscripts/propiedades_entornos.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comunes_'.$idioma.'.js"></SCRIPT>'?>
	
<?

$literal="resultado_modificar_entornos";


if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$identorno.");".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$identorno.")";
	echo '</SCRIPT>';
}

?>
</BODY>
</HTML>	
<?
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla entornos
________________________________________________________________________________________________________*/
function Gestiona()
{
	global	$cmd;

	global	$identorno;
	global	$ipserveradm;
	global	$portserveradm;
	global	$protoclonacion;


	$cmd->CreaParametro("@identorno",$identorno,1);
	$cmd->CreaParametro("@ipserveradm",$ipserveradm,0);
	$cmd->CreaParametro("@portserveradm",$portserveradm,1);
	$cmd->CreaParametro("@protoclonacion",$protoclonacion,0);

	$cmd->texto="UPDATE entornos SET ipserveradm=@ipserveradm,portserveradm=@portserveradm,protoclonacion=@protoclonacion";
	$resul=$cmd->Ejecutar();
	return($resul);
}
?>
