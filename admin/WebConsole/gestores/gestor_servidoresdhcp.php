<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_servidoresdhcp.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de servidoresdhcp
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("./relaciones/servidoresdhcp_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idservidordhcp=0; 
$nombreservidordhcp="";
$ip="";
$passguor="";
$pathdhcpconf="";
$pathdhcpd="";
$grupoid=0;
$comentarios="";

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros

if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"];
if (isset($_GET["idservidordhcp"])) $idservidordhcp=$_GET["idservidordhcp"];
if (isset($_GET["identificador"])) $idservidordhcp=$_GET["identificador"];

if (isset($_GET["nombreservidordhcp"])) $nombreservidordhcp=$_GET["nombreservidordhcp"]; 
if (isset($_GET["ip"])) $ip=$_GET["ip"]; 
if (isset($_GET["passguor"])) $passguor=$_GET["passguor"]; 
if (isset($_GET["pathdhcpconf"])) $pathdhcpconf=$_GET["pathdhcpconf"];
if (isset($_GET["pathdhcpd"])) $pathdhcpd=$_GET["pathdhcpd"]; 
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
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<BODY>
<?
	$literal="";
	switch($opcion){
		case $op_alta :
			$literal="resultado_insertar_servidoresdhcp";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_servidoresdhcp";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_servidoresdhcp";
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
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idservidordhcp.",o.innerHTML);";
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$nombreservidordhcp."');";
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idservidordhcp.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla servidoresdhcp
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$grupoid;

	global	$idservidordhcp;
	global	$nombreservidordhcp;
	global	$ip;
	global	$passguor;
	global	$pathdhcpconf;
	global	$pathdhcpd;
	global	$comentarios;
	
	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;

	global	$tablanodo;

	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@idcentro",$idcentro,1);

	$cmd->CreaParametro("@idservidordhcp",$idservidordhcp,1);
	$cmd->CreaParametro("@nombreservidordhcp",$nombreservidordhcp,0);
	$cmd->CreaParametro("@ip",$ip,0);
	$cmd->CreaParametro("@passguor",$passguor,0);
	$cmd->CreaParametro("@pathdhcpconf",$pathdhcpconf,0);
	$cmd->CreaParametro("@pathdhcpd",$pathdhcpd,0);
	$cmd->CreaParametro("@comentarios",$comentarios,0);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO servidoresdhcp(idcentro,grupoid,nombreservidordhcp,ip,passguor,pathdhcpconf,pathdhcpd,comentarios) VALUES (@idcentro,@grupoid,@nombreservidordhcp,@ip,@passguor,@pathdhcpconf,@pathdhcpd,@comentarios)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idservidordhcp=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_servidoresdhcp($idservidordhcp,$nombreservidordhcp);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE servidoresdhcp SET nombreservidordhcp=@nombreservidordhcp,ip=@ip,passguor=@passguor,pathdhcpconf=@pathdhcpconf,pathdhcpd=@pathdhcpd,comentarios=@comentarios WHERE idservidordhcp=@idservidordhcp";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaServidoresdhcp($cmd,$idservidordhcp,"idservidordhcp");
			break;
		case $op_movida :
			$cmd->texto="UPDATE servidoresdhcp SET  grupoid=@grupoid WHERE idservidordhcp=@idservidordhcp";
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
function SubarbolXML_servidoresdhcp($idservidordhcp,$nombreservidordhcp){
	global $LITAMBITO_SERVIDORESDHCP;
	$cadenaXML='<SERVIDORDHCP';
	// Atributos			
	$cadenaXML.=' imagenodo="../images/iconos/servidordhcp.gif" ';
	$cadenaXML.=' infonodo="'.$nombreservidordhcp.'"';
	$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_SERVIDORESDHCP."'" .')"';
	$cadenaXML.=' nodoid='.$LITAMBITO_SERVIDORESDHCP.'-'.$idservidordhcp;
	$cadenaXML.='>';
	$cadenaXML.='</SERVIDORDHCP>';
	return($cadenaXML);
}
?>