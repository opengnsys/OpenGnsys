<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_perfilsoftwares.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de perfilessoft
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("./relaciones/perfilessoft_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idperfilsoft=0; 
$descripcion="";
$comentarios="";
$grupoid=0; 

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros

if (isset($_POST["idperfilsoft"])) $idperfilsoft=$_POST["idperfilsoft"];
if (isset($_POST["descripcion"])) $descripcion=$_POST["descripcion"]; 
if (isset($_POST["comentarios"])) $comentarios=$_POST["comentarios"]; 
if (isset($_POST["grupoid"])) $grupoid=$_POST["grupoid"];
if (isset($_POST["identificador"])) $idperfilsoft=$_POST["identificador"];

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
	echo '	<SCRIPT language="javascript" src="../jscripts/propiedades_perfilsoftwares.js"></SCRIPT>';
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
		$literal="resultado_insertar_perfilsoftwares";
		break;
	case $op_modificacion:
		$literal="resultado_modificar_perfilsoftwares";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_perfilsoftwares";
		break;
	case $op_movida :
		$literal="resultado_mover";
		break;	
	default:
		break;
}
if ($resul){
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idperfilsoft.",o.innerHTML);".chr(13);
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');".chr(13);
}
else
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idperfilsoft.")";

if($opcion!=$op_movida){
	echo '	</SCRIPT>';
	echo '</BODY>	';
	echo '</HTML>';	
}
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla perfilessoft
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$idperfilsoft;
	global	$descripcion;
	global	$comentarios;
	global	$grupoid;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;

	global	$tablanodo;

	$cmd->CreaParametro("@idcentro",$idcentro,1);
	$cmd->CreaParametro("@idperfilsoft",$idperfilsoft,1);
	$cmd->CreaParametro("@descripcion",$descripcion,0);
	$cmd->CreaParametro("@comentarios",$comentarios,0);
	$cmd->CreaParametro("@grupoid",$grupoid,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO perfilessoft (descripcion,comentarios,idcentro,grupoid) VALUES (@descripcion,@comentarios,@idcentro,@grupoid)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idperfilsoft=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_perfilessoft($idperfilsoft,$descripcion);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE perfilessoft SET descripcion=@descripcion,comentarios=@comentarios WHERE idperfilsoft=@idperfilsoft";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaPerfilessoft($cmd,$idperfilsoft,"idperfilsoft");
			break;
		case $op_movida :
			$cmd->texto="UPDATE perfilessoft SET  grupoid=@grupoid WHERE idperfilsoft=@idperfilsoft";
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
function SubarbolXML_perfilessoft($idperfilsoft,$descripcion){
		global $LITAMBITO_PERFILESSOFT;
		$cadenaXML='<PERFILESSOFTWARES ';
		// Atributos		
		$cadenaXML.=' imagenodo="../images/iconos/perfilsoftware.gif"';
		$cadenaXML.=' infonodo="' .$descripcion.'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_PERFILESSOFT.'-'.$idperfilsoft;
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_PERFILESSOFT."'" .')"';
		$cadenaXML.='>';
		$cadenaXML.='</PERFILESSOFTWARES>';
		return($cadenaXML);
}

