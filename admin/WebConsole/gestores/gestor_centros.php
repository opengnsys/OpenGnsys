<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_centros.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de centros
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("./relaciones/centros_eliminacion.php");
include_once("../includes/opciones.php");
include_once("./relaciones/centros_eliminacion.php");
include_once("./relaciones/aulas_eliminacion.php");
include_once("./relaciones/ordenadores_eliminacion.php");
include_once("./relaciones/gruposordenadores_eliminacion.php");

//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$identidad=0; 
$idcentro=0; 
$nombrecentro="";
$comentarios="";

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros
if (isset($_POST["identidad"])) $identidad=$_POST["identidad"];
if (isset($_POST["idcentro"])) $idcentro=$_POST["idcentro"];
if (isset($_POST["identificador"])) $idcentro=$_POST["identificador"];
if (isset($_POST["nombrecentro"])) $nombrecentro=$_POST["nombrecentro"];
if (isset($_POST["comentarios"])) $comentarios=$_POST["comentarios"];


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
	<SCRIPT language="javascript" src="../jscripts/propiedades_centros.js"></SCRIPT>
<?
	$literal="";
	switch($opcion){
		case $op_alta :
			$literal="resultado_insertar_centros";
			break;
		case $op_modificacion:
			$literal="resultado_modificar_centros";
			break;
		case $op_eliminacion :
			$literal="resultado_eliminar_centros";
			break;
		case $op_movida :
			$literal="resultado_cambiar_centros";
			break;
		default:
			break;
	}
	echo '<P><SPAN style="visibility:hidden" id="arbol_nodo">'.$tablanodo.'</SPAN></P>';
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo 'var oHTML'.chr(13);
	echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
	echo 'o=cTBODY.item(1);'.chr(13);
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idcentro.",o.innerHTML);".chr(13);
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$nombrecentro."');".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idcentro.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
/*________________________________________________________________________________________________________
	Inserta, modifica o elimina datos en la tabla centros
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global $idcentro;
	global $nombrecentro;
	global $comentarios;
	global $identidad;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;
	global	$tablanodo;

	$cmd->CreaParametro("@identidad",$identidad,1);
	$cmd->CreaParametro("@idcentro",$idcentro,1);
	$cmd->CreaParametro("@nombrecentro",$nombrecentro,0);
	$cmd->CreaParametro("@comentarios",$comentarios,0);
	
	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO centros(nombrecentro,comentarios,identidad) VALUES (@nombrecentro,@comentarios,@identidad)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idcentro=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_centros($idcentro,$nombrecentro);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE centros SET nombrecentro=@nombrecentro,comentarios=@comentarios WHERE idcentro=@idcentro";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaCentros($cmd,$idcentro,"idcentro");// Eliminación en cascada
			break;
		case $op_movida :
			$cmd->texto="UPDATE centros SET identidad=@identidad WHERE idcentro=@idcentro";
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
function SubarbolXML_centros($idcentro,$nombrecentro){
		global $LITAMBITO_CENTROS;
		$cadenaXML='<CENTRO';
		// Atributos			
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_CENTROS."'" .')"';
		$cadenaXML.=' imagenodo="../images/iconos/centros.gif"';
		$cadenaXML.=' infonodo="'.$nombrecentro.'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_CENTROS.'-'.$idcentro;
		$cadenaXML.='></CENTRO>';
		return($cadenaXML);
} 
?>
