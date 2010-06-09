<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_ordenadores.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de ordenadores
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("./relaciones/ordenadores_eliminacion.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$grupoid=0; 
$idaula=0; 
$idordenador=0; 
$nombreordenador="";
$ip="";
$mac="";
$idperfilhard=0;
$idservidordhcp=0;
$idservidorrembo=0;
$idmenu=0;
$idimagen=0;
$cache=0;
$modomul=0;
$ipmul="";
$pormul=0;
$velmul=0;

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"];
if (isset($_GET["idaula"])) $idaula=$_GET["idaula"];
if (isset($_GET["idordenador"])) $idordenador=$_GET["idordenador"];
if (isset($_GET["identificador"])) $idordenador=$_GET["identificador"];
if (isset($_GET["nombreordenador"])) $nombreordenador=$_GET["nombreordenador"];
if (isset($_GET["ip"])) $ip=$_GET["ip"];
if (isset($_GET["mac"])) $mac=$_GET["mac"];
if (isset($_GET["idperfilhard"])) $idperfilhard=$_GET["idperfilhard"];
if (isset($_GET["idservidordhcp"])) $idservidordhcp=$_GET["idservidordhcp"];
if (isset($_GET["idservidorrembo"])) $idservidorrembo=$_GET["idservidorrembo"];
if (isset($_GET["idmenu"])) $idmenu=$_GET["idmenu"];
if (isset($_GET["cache"])) $cache=$_GET["cache"];
if (isset($_GET["modomul"])) $modomul=$_GET["modomul"];
if (isset($_GET["ipmul"])) $ipmul=$_GET["ipmul"];
if (isset($_GET["pormul"])) $pormul=$_GET["pormul"];
if (isset($_GET["velmul"])) $velmul=$_GET["velmul"];

if(empty($cache)) $cache=0;

$tablanodo=""; // Arbol para nodos insertados

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	if ($idaula==0) 
		$idaula=toma_aula($cmd,$grupoid);
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
			$literal="resultado_insertar_ordenadores";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_ordenadores";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_ordenadores";
			break;
		case $op_movida :
			$literal="resultado_cambiar_ordenadores";
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
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idordenador.",o.innerHTML);".chr(13);
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$nombreordenador."');".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idordenador.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
/**************************************************************************************************************************************************
	Busca identificador del aula de un grupo de ordenador .Devuelve el identificador del aula a la que pertenece el grupo 
		Parametros: 
		- cmd:Una comando ya operativo (con conexión abierta)  
________________________________________________________________________________________________________*/
function toma_aula($cmd,$idgrupo){
	$rs=new Recordset; 
	$cmd->texto="SELECT idaula FROM gruposordenadores WHERE idgrupo=".$idgrupo;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF)
		return($rs->campos["idaula"]);
	else
		return(0);
}
/*________________________________________________________________________________________________________
	Inserta, modifica o elimina datos en la tabla ordenadores
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global $grupoid;
	global $idordenador;
	global $nombreordenador;
	global $ip;
	global $mac;
	global $idaula;
	global $idperfilhard;
	global $idservidordhcp;
	global $idservidorrembo;
	global $idmenu;
	global $cache;
	global $modomul;
	global $ipmul;
	global $pormul;
	global $velmul;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;
	global	$tablanodo;

	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@idaula",$idaula,1);
	$cmd->CreaParametro("@idordenador",$idordenador,1);
	$cmd->CreaParametro("@nombreordenador",$nombreordenador,0);
	$cmd->CreaParametro("@ip",$ip,0);
	$cmd->CreaParametro("@mac",$mac,0);
	$cmd->CreaParametro("@idperfilhard",$idperfilhard,1);
	$cmd->CreaParametro("@idservidordhcp",$idservidordhcp,1);
	$cmd->CreaParametro("@idservidorrembo",$idservidorrembo,1);
	$cmd->CreaParametro("@idmenu",$idmenu,1);
	$cmd->CreaParametro("@cache",$cache,1);
	$cmd->CreaParametro("@modomul",$modomul,0);
	$cmd->CreaParametro("@dipmul",$ipmul,0);
	$cmd->CreaParametro("@pormul",$pormul,1);
	$cmd->CreaParametro("@velmul",$velmul,1);	

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO ordenadores
									(nombreordenador,ip,mac,idperfilhard,idservidordhcp,idservidorrembo,
									idmenu,idaula,grupoid,idconfiguracion,cache,modomul,ipmul,pormul,velmul) 
							VALUES
									(@nombreordenador,@ip,@mac,@idperfilhard,@idservidordhcp,@idservidorrembo,
									@idmenu,@idaula,@grupoid,0,@cache,@modomul,@dipmul,@pormul,@velmul)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idordenador=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_ordenadores($idordenador,$nombreordenador);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE ordenadores SET
										nombreordenador=@nombreordenador,ip=@ip,mac=@mac,
										idperfilhard=@idperfilhard,idservidordhcp=@idservidordhcp,
										idservidorrembo=@idservidorrembo,idmenu=@idmenu,cache=@cache,
										modomul=@modomul,ipmul=@dipmul,pormul=@pormul,velmul=@velmul
							WHERE
										idordenador=@idordenador";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaOrdenadores($cmd,$idordenador,"idordenador");// Eliminación en cascada
			break;
		case $op_movida :
			$cmd->texto="UPDATE ordenadores SET idaula=@idaula, grupoid=@grupoid WHERE idordenador=@idordenador";
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
function SubarbolXML_ordenadores($idordenador,$nombreordenador){
		global $LITAMBITO_ORDENADORES;
		$cadenaXML='<ORDENADOR';
		// Atributos			
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_ORDENADORES."'" .')"';
		$cadenaXML.=' imagenodo="../images/iconos/ordenador.gif"';
		$cadenaXML.=' infonodo="'.$nombreordenador.'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_ORDENADORES.'-'.$idordenador;
		$cadenaXML.='></ORDENADOR>';
		return($cadenaXML);
} 
?>
