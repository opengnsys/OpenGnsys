<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_componentehardwares.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de hardwares
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("./relaciones/hardwares_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idhardware=0; 
$descripcion="";
$idtipohardware=0;
$grupoid=0; 

$urlimgth=""; // Url de la imagen del tipo de hardware al que pertenece el componente

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros

if (isset($_POST["idhardware"])) $idhardware=$_POST["idhardware"];
if (isset($_POST["descripcion"])) $descripcion=$_POST["descripcion"]; 
if (isset($_POST["idtipohardware"])) $idtipohardware=$_POST["idtipohardware"]; 
if (isset($_POST["grupoid"])) $grupoid=$_POST["grupoid"];
if (isset($_POST["identificador"])) $idhardware=$_POST["identificador"];

$tablanodo=""; // Arbol para nodos insertados

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
if($opcion!=$op_movida){
	echo '<HTML>';
	echo '<HEAD>';
	echo '	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">';
	echo '<BODY>';
	echo '<P><SPAN style="visibility:hidden" id="arbol_nodo">'.$tablanodo.'</SPAN></P>';
	echo '	<SCRIPT language="javascript" src="../jscripts/propiedades_componentehardwares.js"></SCRIPT>';
	echo '<SCRIPT language="javascript">'.chr(13);
	if ($resul){
		echo 'var oHTML'.chr(13);
		echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
		echo 'o=cTBODY.item(1);'.chr(13);
	}
}
$literal="";
switch($opcion){
	case $op_alta :
		$literal="resultado_insertar_componentehardwares";
		break;
	case $op_modificacion:
		$literal="resultado_modificar_componentehardwares";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_componentehardwares";
		break;
	case $op_movida :
		$literal="resultado_mover";
		break;		
	default:
		break;
}
if ($resul){
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idhardware.",o.innerHTML);".chr(13);
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');".chr(13);
}
else
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idhardware.")";

if($opcion!=$op_movida){
	echo '	</SCRIPT>';
	echo '</BODY>	';
	echo '</HTML>';	
}
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla hardwares
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$idhardware;
	global	$descripcion;
	global	$idtipohardware;
	global	$grupoid;

	global $urlimgth;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;

	global	$tablanodo;

	$cmd->CreaParametro("@idcentro",$idcentro,1);
	$cmd->CreaParametro("@idhardware",$idhardware,1);
	$cmd->CreaParametro("@descripcion",$descripcion,0);
	$cmd->CreaParametro("@idtipohardware",$idtipohardware,1);
	$cmd->CreaParametro("@grupoid",$grupoid,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO hardwares (descripcion,idtipohardware,idcentro,grupoid) VALUES (@descripcion,@idtipohardware,@idcentro,@grupoid)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idhardware=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_ComponenteHardwares($cmd,$idhardware,$descripcion,$idtipohardware);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE hardwares SET descripcion=@descripcion,idtipohardware=@idtipohardware WHERE idhardware=@idhardware";
			$resul=$cmd->Ejecutar();
			if ($resul) // Toma la imagen del tipo de componente hardware
					$urlimgth=TomaDato($cmd,0,'tipohardwares',$idtipohardware,'idtipohardware','urlimg');
			break;
		case $op_eliminacion :
			$resul=EliminaHardwares($cmd,$idhardware,"idhardware");
			break;
		case $op_movida :
			$cmd->texto="UPDATE hardwares SET  grupoid=@grupoid WHERE idhardware=@idhardware";
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
function SubarbolXML_ComponenteHardwares($cmd,$idhardware,$descripcion,$idtipohardware){
		global $LITAMBITO_COMPONENTESHARD;
		$urlimg=TomaDato($cmd,0,'tipohardwares',$idtipohardware,'idtipohardware','urlimg');
		$cadenaXML='<COMPONENTEHARDWARES';
		// Atributos
		if	(!empty($urlimg))
				$cadenaXML.=' imagenodo="'.$urlimg.'"';
			else
				$cadenaXML.=' imagenodo="../images/iconos/confihard.gif"';	
		$cadenaXML.=' infonodo="'.$descripcion.'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_COMPONENTESHARD.'-'.$idhardware;
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_COMPONENTESHARD."'" .')"';
		$cadenaXML.='>';
		$cadenaXML.='</COMPONENTEHARDWARES>';
		return($cadenaXML);
}
?>
