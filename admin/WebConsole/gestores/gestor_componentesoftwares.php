<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_componentesoftwares.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de softwares
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("./relaciones/softwares_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idsoftware=0; 
$descripcion="";
$idtiposoftware=0;
$idtiposo=0;
$grupoid=0; 

$urlimgth=""; // Url de la imagen del tipo de software al que pertenece el componente

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros

if (isset($_GET["idsoftware"])) $idsoftware=$_GET["idsoftware"];
if (isset($_GET["descripcion"])) $descripcion=$_GET["descripcion"]; 
if (isset($_GET["idtiposoftware"])) $idtiposoftware=$_GET["idtiposoftware"]; 
if (isset($_GET["idtiposo"])) $idtiposo=$_GET["idtiposo"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"];
if (isset($_GET["identificador"])) $idsoftware=$_GET["identificador"];

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
			$literal="resultado_insertar_componentesoftwares";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_componentesoftwares";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_componentesoftwares";
			break;
		case $op_movida :
			$literal="resultado_mover";
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
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idsoftware.",o.innerHTML);".chr(13);
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idsoftware.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla softwares
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$idsoftware;
	global	$descripcion;
	global	$idtiposoftware;
	global	$idtiposo;
	global	$grupoid;

	global $urlimgth;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;

	global	$tablanodo;

	$cmd->CreaParametro("@idcentro",$idcentro,1);
	$cmd->CreaParametro("@idsoftware",$idsoftware,1);
	$cmd->CreaParametro("@descripcion",$descripcion,0);
	$cmd->CreaParametro("@idtiposoftware",$idtiposoftware,1);
	$cmd->CreaParametro("@idtiposo",$idtiposo,1);
	$cmd->CreaParametro("@grupoid",$grupoid,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO softwares (descripcion,idtiposoftware,idtiposo,idcentro,grupoid) VALUES (@descripcion,@idtiposoftware,@idtiposo,@idcentro,@grupoid)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idsoftware=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_Componentesoftwares($cmd,$idsoftware,$descripcion,$idtiposoftware);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE softwares SET descripcion=@descripcion,idtiposoftware=@idtiposoftware,idtiposo=@idtiposo WHERE idsoftware=@idsoftware";
			$resul=$cmd->Ejecutar();
			if ($resul) // Toma la imagen del tipo de componente software
					$urlimgth=TomaDato($cmd,0,'tiposoftwares',$idtiposoftware,'idtiposoftware','urlimg');
			break;
		case $op_eliminacion :
			$resul=EliminaSoftwares($cmd,$idsoftware,"idsoftware");
			break;
		case $op_movida :
			$cmd->texto="UPDATE softwares SET  grupoid=@grupoid WHERE idsoftware=@idsoftware";
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
function SubarbolXML_Componentesoftwares($cmd,$idsoftware,$descripcion,$idtiposoftware){
		global $LITAMBITO_COMPONENTESSOFT;
		$urlimg=TomaDato($cmd,0,'tiposoftwares',$idtiposoftware,'idtiposoftware','urlimg');
		$cadenaXML='<COMPONENTESOFTWARES';
		// Atributos
		if	(!empty($urlimg))
				$cadenaXML.=' imagenodo="'.$urlimg.'"';
			else
				$cadenaXML.=' imagenodo="../images/iconos/confisoft.gif"';	
		$cadenaXML.=' infonodo="'.$descripcion.'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_COMPONENTESSOFT.'-'.$idsoftware;
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_COMPONENTESSOFT."'" .')"';
		$cadenaXML.='>';
		$cadenaXML.='</COMPONENTESOFTWARES>';
		return($cadenaXML);
}
?>