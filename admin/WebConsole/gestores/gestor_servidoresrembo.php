<?
// ******************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaciónn: A�o 2003-2004
// Fecha �ltima modificaci�n: Marzo-2005
// Nombre del fichero: gestor_servidoresrembo.php
// Descripciónn :
//		Gestiona el mantenimiento de la tabla de servidoresrembo
// ******************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("./relaciones/servidoresrembo_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idservidorrembo=0; 
$nombreservidorrembo="";
$ip="";
$passguor="";
$pathremboconf="";
$pathrembod="";
$pathpxe="";

$grupoid=0;
$puertorepo="";
$comentarios="";

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros

if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"];
if (isset($_GET["idservidorrembo"])) $idservidorrembo=$_GET["idservidorrembo"];
if (isset($_GET["identificador"])) $idservidorrembo=$_GET["identificador"];

if (isset($_GET["nombreservidorrembo"])) $nombreservidorrembo=$_GET["nombreservidorrembo"]; 
if (isset($_GET["ip"])) $ip=$_GET["ip"]; 
if (isset($_GET["passguor"])) $passguor=$_GET["passguor"]; 
if (isset($_GET["pathremboconf"])) $pathremboconf=$_GET["pathremboconf"];
if (isset($_GET["pathrembod"])) $pathrembod=$_GET["pathrembod"]; 
if (isset($_GET["pathpxe"])) $pathpxe=$_GET["pathpxe"]; 
if (isset($_GET["puertorepo"])) $puertorepo=$_GET["puertorepo"];
if (isset($_GET["comentarios"])) $comentarios=$_GET["comentarios"];

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
<BODY>
<?
	$literal="";
	switch($opcion){
		case $op_alta :
			$literal="resultado_insertar_servidoresrembo";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_servidoresrembo";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_servidoresrembo";
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
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idservidorrembo.",o.innerHTML);";
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$nombreservidorrembo."');";
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idservidorrembo.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla servidoresrembo
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$grupoid;

	global	$idservidorrembo;
	global	$nombreservidorrembo;
	global	$ip;
	global	$passguor;
	global	$pathremboconf;
	global	$pathrembod;
	global	$pathpxe;
	global  $puertorepo;
	global	$comentarios;
	
	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;

	global	$tablanodo;

	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@idcentro",$idcentro,1);

	$cmd->CreaParametro("@idservidorrembo",$idservidorrembo,1);
	$cmd->CreaParametro("@nombreservidorrembo",$nombreservidorrembo,0);
	$cmd->CreaParametro("@ip",$ip,0);
	$cmd->CreaParametro("@passguor",$passguor,0);
	$cmd->CreaParametro("@pathremboconf",$pathremboconf,0);
	$cmd->CreaParametro("@pathrembod",$pathrembod,0);
	$cmd->CreaParametro("@pathpxe",$pathpxe,0);
	$cmd->CreaParametro("@puertorepo",$puertorepo,0);
	$cmd->CreaParametro("@comentarios",$comentarios,0);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO servidoresrembo(idcentro,grupoid,nombreservidorrembo,ip,passguor,pathremboconf,pathrembod,pathpxe,puertorepo,comentarios) VALUES (@idcentro,@grupoid,@nombreservidorrembo,@ip,@passguor,@pathremboconf,@pathrembod,@pathpxe,@puertorepo,@comentarios)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la p�gina que llam� �sta
				$idservidorrembo=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_servidoresrembo($idservidorrembo,$nombreservidorrembo);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del �rbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE servidoresrembo SET nombreservidorrembo=@nombreservidorrembo,ip=@ip,passguor=@passguor,pathremboconf=@pathremboconf,pathrembod=@pathrembod,pathpxe=@pathpxe,puertorepo=@puertorepo,comentarios=@comentarios WHERE idservidorrembo=@idservidorrembo";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaServidoresrembo($cmd,$idservidorrembo,"idservidorrembo");
			break;
		case $op_movida :
			$cmd->texto="UPDATE servidoresrembo SET  grupoid=@grupoid WHERE idservidorrembo=@idservidorrembo";
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
function SubarbolXML_servidoresrembo($idservidorrembo,$nombreservidorrembo){
	global $LITAMBITO_SERVIDORESREMBO;
	$cadenaXML='<SERVIDORREMBO';
	// Atributos			
	$cadenaXML.=' imagenodo="../images/iconos/servidorrembo.gif" ';
	$cadenaXML.=' infonodo="'.$nombreservidorrembo.'"';
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_SERVIDORESREMBO."'" .')"';
	$cadenaXML.=' nodoid='.$LITAMBITO_SERVIDORESREMBO.'-'.$idservidorrembo;
	$cadenaXML.='>';
	$cadenaXML.='</SERVIDORREMBO>';
	return($cadenaXML);
}
?>
