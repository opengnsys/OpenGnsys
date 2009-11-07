<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_tipohardwares.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de tipohardwares
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idtipohardware=0; 
$descripcion="";
$urlimg="";
$urlicono="";

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros

if (isset($_GET["idtipohardware"])) $idtipohardware=$_GET["idtipohardware"];
if (isset($_GET["descripcion"])) $descripcion=$_GET["descripcion"]; 
if (isset($_GET["urlicono"])) $urlicono=$_GET["urlicono"]; 

if(empty($urlicono))
	$urlimg="../images/iconos/confihard.gif";
else
	$urlimg="../images/iconos/".$urlicono;

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
		case $op_alta :
			$literal="resultado_insertar_tipohardwares";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_tipohardwares";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_tipohardwares";
			break;
		default:
			break;
	}
echo '<p><span id="arbol_nodo">'.$tablanodo.'</span></p>';
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo 'var oHTML'.chr(13);
	echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
	echo 'o=cTBODY.item(1);'.chr(13);
	if ($opcion==$op_alta )
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idtipohardware.",o.innerHTML);";
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');";
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idtipohardware.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla tipohardwares
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$idtipohardware;
	global	$descripcion;
	global	$urlimg;
	
	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$tablanodo;

	$cmd->CreaParametro("@idtipohardware",$idtipohardware,1);
	$cmd->CreaParametro("@descripcion",$descripcion,0);
	$cmd->CreaParametro("@urlimg",$urlimg,0);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO tipohardwares(descripcion,urlimg) VALUES (@descripcion,@urlimg)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idtipohardware=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_tipohardwares($idtipohardware,$descripcion,$urlimg);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE tipohardwares SET descripcion=@descripcion,urlimg=@urlimg WHERE idtipohardware=@idtipohardware";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$cmd->texto="DELETE  FROM tipohardwares WHERE idtipohardware=".$idtipohardware;
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}
/*________________________________________________________________________________________________________
	Crea un arbol XML para el nuevo nodo insertado 
________________________________________________________________________________________________________*/
function SubarbolXML_tipohardwares($idtipohardware,$descripcion,$urlimg){
		global 	$LITAMBITO_TIPOHARDWARES;
		$cadenaXML.='<TIPOHARDWARES';
		// Atributos
		if	($urlimg)
				$cadenaXML.=' imagenodo='.$urlimg;
			else
				$cadenaXML.=' imagenodo="../images/iconos/confihard.gif"';	
		$cadenaXML.=' infonodo="'.$descripcion.'"';
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_TIPOHARDWARES."'" .')"';
		$cadenaXML.=' nodoid='.$LITAMBITO_TIPOHARDWARES.'-'.$idtipohardware;
		$cadenaXML.='>';
		$cadenaXML.='</TIPOHARDWARES>';
		return($cadenaXML);
}
?>