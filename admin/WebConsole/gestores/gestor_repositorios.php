<?php
// ******************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaciónn: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_repositorios.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de repositorios
// ******************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("./relaciones/repositorios_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idrepositorio=0; 
$nombrerepositorio="";
$ip="";
$passguor="";

$grupoid=0;
$puertorepo="";
$apiKeyRepo="";
$comentarios="";

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros

if (isset($_POST["grupoid"])) $grupoid=$_POST["grupoid"];
if (isset($_POST["idrepositorio"])) $idrepositorio=$_POST["idrepositorio"];
if (isset($_POST["identificador"])) $idrepositorio=$_POST["identificador"];

if (isset($_POST["nombrerepositorio"])) $nombrerepositorio=$_POST["nombrerepositorio"]; 
if (isset($_POST["ip"])) $ip=$_POST["ip"]; 
if (isset($_POST["passguor"])) $passguor=$_POST["passguor"]; 
if (isset($_POST["puertorepo"])) $puertorepo=$_POST["puertorepo"];
if (isset($_POST["apiKeyRepo"])) $apiKeyRepo=$_POST["apiKeyRepo"];
if (isset($_POST["comentarios"])) $comentarios=$_POST["comentarios"];

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
	echo '	<SCRIPT language="javascript" src="../jscripts/propiedades_repositorios.js"></SCRIPT>';
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
		$literal="resultado_insertar_repositorios";
		break;
	case $op_modificacion:
		$literal="resultado_modificar_repositorios";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_repositorios";
		break;
	case $op_movida :
		$literal="resultado_mover";
		break;
	default:
		break;
}
if ($resul){
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idrepositorio.",o.innerHTML);".chr(13);
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$nombrerepositorio."');".chr(13);
}
else
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idrepositorio.")";

if($opcion!=$op_movida){
	echo '	</SCRIPT>';
	echo '</BODY>	';
	echo '</HTML>';	
}
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla repositorios
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$grupoid;

	global	$idrepositorio;
	global	$nombrerepositorio;
	global	$ip;
	global	$passguor;
	global  $puertorepo;
	global  $apiKeyRepo;
	global	$comentarios;
	
	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;

	global	$tablanodo;

	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@idcentro",$idcentro,1);

	$cmd->CreaParametro("@idrepositorio",$idrepositorio,1);
	$cmd->CreaParametro("@nombrerepositorio",$nombrerepositorio,0);
	$cmd->CreaParametro("@ip",$ip,0);
	$cmd->CreaParametro("@passguor",$passguor,0);
	$cmd->CreaParametro("@puertorepo",$puertorepo,0);
	$cmd->CreaParametro("@apiKeyRepo",$apiKeyRepo,0);
	$cmd->CreaParametro("@comentarios",$comentarios,0);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO repositorios(idcentro,grupoid,nombrerepositorio,ip,passguor,puertorepo,comentarios,apikey) VALUES (@idcentro,@grupoid,@nombrerepositorio,@ip,@passguor,@puertorepo,@comentarios,@apiKeyRepo)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la p�gina que llam� �sta
				$idrepositorio=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_repositorios($idrepositorio,$nombrerepositorio);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del �rbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE repositorios SET nombrerepositorio=@nombrerepositorio,ip=@ip,passguor=@passguor,puertorepo=@puertorepo,comentarios=@comentarios, apikey=@apiKeyRepo WHERE idrepositorio=@idrepositorio";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=Eliminarepositorios($cmd,$idrepositorio,"idrepositorio");
			break;
		case $op_movida :
			$cmd->texto="UPDATE repositorios SET  grupoid=@grupoid WHERE idrepositorio=@idrepositorio";
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}
//________________________________________________________________________________________________________
//	Crea un arbol XML para el nuevo grupo insertado 
//________________________________________________________________________________________________________
function SubarbolXML_repositorios($idrepositorio,$nombrerepositorio){
	global $LITAMBITO_REPOSITORIOS;
	$cadenaXML='<REPOSITORIO';
	// Atributos			
	$cadenaXML.=' imagenodo="../images/iconos/repositorio.gif" ';
	$cadenaXML.=' infonodo="'.$nombrerepositorio.'"';
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_REPOSITORIOS."'" .')"';
	$cadenaXML.=' nodoid='.$LITAMBITO_REPOSITORIOS.'-'.$idrepositorio;
	$cadenaXML.='>';
	$cadenaXML.='</REPOSITORIO>';
	return($cadenaXML);
}
?>
