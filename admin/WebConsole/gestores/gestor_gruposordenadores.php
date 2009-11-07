<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_gruposordenadores.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de gruposordenadores
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("./relaciones/ordenadores_eliminacion.php");
include_once("../includes/opciones.php");
include_once("./relaciones/gruposordenadores_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros
$nombregrupoordenador=""; 
$grupoid=0; 
$idgrupo=0; 
$idaula=0; 
$comentarios="";

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["nombregrupo"])) $nombregrupoordenador=$_GET["nombregrupo"];
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"];
if (isset($_GET["idgrupo"])) $idgrupo=$_GET["idgrupo"];
if (isset($_GET["idaula"])) $idaula=$_GET["idaula"];
if (isset($_GET["comentarios"])) $comentarios=$_GET["comentarios"];

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
			$literal="resultado_insertar_grupos";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_grupos";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_grupos";
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
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idgrupo.",o.innerHTML);".chr(13);
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$nombregrupoordenador."');".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idgrupo.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
/**************************************************************************************************************************************************
	Busca identificador del aula cuando el grupo a crear está pertenece a otro grupo 
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
	Inserta, modifica o elimina datos en la tabla gruposordenadores
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;
	global	$idaula;
	global	$nombregrupoordenador;
	global	$grupoid;
	global	$idgrupo;
	global	$comentarios;
	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$tablanodo;

	$cmd->CreaParametro("@nombregrupoordenador",$nombregrupoordenador,0);
	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@idaula",$idaula,1);
	$cmd->CreaParametro("@idgrupo",$idgrupo,1);
	$cmd->CreaParametro("@comentarios",$comentarios,0);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO gruposordenadores(nombregrupoordenador,idaula,grupoid,comentarios) VALUES (@nombregrupoordenador,@idaula,@grupoid,@comentarios)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idgrupo=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_grupos_ordenadores($idgrupo,$nombregrupoordenador);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE gruposordenadores SET nombregrupoordenador=@nombregrupoordenador,comentarios=@comentarios WHERE idgrupo=@idgrupo";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaGruposOrdenadores($cmd,$idgrupo,"idgrupo");
			break;
		default:
			break;
	}
	return($resul);
}
/*________________________________________________________________________________________________________
	Crea un arbol XML para el nuevo grupo insertado 
________________________________________________________________________________________________________*/
function SubarbolXML_grupos_ordenadores($idgrupo,$nombregrupoordenador){
		global 	$LITAMBITO_GRUPOSORDENADORES;
		$cadenaXML='<GRUPOSORDENADORES ';
		// Atributos		
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_GRUPOSORDENADORES."'" .')"';
		$cadenaXML.=' imagenodo="../images/iconos/carpeta.gif"';
		$cadenaXML.=' infonodo="'.$nombregrupoordenador.'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_GRUPOSORDENADORES.'-'.$idgrupo;
		$cadenaXML.='>';
		$cadenaXML.='</GRUPOSORDENADORES>';
		return($cadenaXML);
}
?>