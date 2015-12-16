<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_entidades.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de entidades
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("./relaciones/entidades_eliminacion.php");
include_once("../includes/opciones.php");
include_once("./relaciones/centros_eliminacion.php");
include_once("./relaciones/aulas_eliminacion.php");
include_once("./relaciones/ordenadores_eliminacion.php");
include_once("./relaciones/gruposordenadores_eliminacion.php");

//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$grupoid=0; 
$iduniversidad=0; 
$identidad=0; 
$nombreentidad="";
$comentarios="";

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros
if (isset($_POST["grupoid"])) $grupoid=$_POST["grupoid"];
if (isset($_POST["iduniversidad"])) $iduniversidad=$_POST["iduniversidad"];
if (isset($_POST["identidad"])) $identidad=$_POST["identidad"];
if (isset($_POST["identificador"])) $identidad=$_POST["identificador"];
if (isset($_POST["nombreentidad"])) $nombreentidad=$_POST["nombreentidad"];
if (isset($_POST["comentarios"])) $comentarios=$_POST["comentarios"];
if (isset($_POST["ogunit"])) $ogunit=$_POST["ogunit"];


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
	echo '</HEAD>';
	echo '<BODY>';
	echo '<P><SPAN style="visibility:hidden" id="arbol_nodo">'.$tablanodo.'</SPAN></P>';
	echo '	<SCRIPT language="javascript" src="../jscripts/propiedades_entidades.js"></SCRIPT>';
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
		$literal="resultado_insertar_entidades";
		break;
	case $op_modificacion:
		$literal="resultado_modificar_entidades";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_entidades";
		break;
	case $op_movida :
		$literal="resultado_cambiar_entidades";
		break;
	default:
		break;
}
if ($resul){
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$identidad.",o.innerHTML);".chr(13);
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$nombreentidad."');".chr(13);
}
else
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$identidad.")";

if($opcion!=$op_movida){
	echo '	</SCRIPT>';
	echo '</BODY>	';
	echo '</HTML>';	
}
/*________________________________________________________________________________________________________
	Inserta, modifica o elimina datos en la tabla entidades
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global $identidad;
	global $nombreentidad;
	global $comentarios;
	global $ogunit;
	global $grupoid;
	global $iduniversidad;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;
	global	$tablanodo;

	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@iduniversidad",$iduniversidad,1);
	$cmd->CreaParametro("@identidad",$identidad,1);
	$cmd->CreaParametro("@nombreentidad",$nombreentidad,0);
	$cmd->CreaParametro("@comentarios",$comentarios,0);
	$cmd->CreaParametro("@ogunit",$ogunit,0);
	
	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO entidades(nombreentidad,comentarios,ogunit,iduniversidad,grupoid) VALUES (@nombreentidad,@comentarios,@ogunit,@iduniversidad,@grupoid)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$identidad=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_entidades($identidad,$nombreentidad);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE entidades SET nombreentidad=@nombreentidad,comentarios=@comentarios,ogunit=@ogunit WHERE identidad=@identidad";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaEntidad($cmd,$identidad,"identidad");// Eliminación en cascada
			break;
		case $op_movida :
			$cmd->texto="UPDATE entidades SET iduniversidad=@iduniversidad, grupoid=@grupoid WHERE identidad=@identidad";
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
function SubarbolXML_entidades($identidad,$nombreentidad){
		global $LITAMBITO_ENTIDADES;
		$cadenaXML='<ENTIDAD';
		// Atributos			
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_ENTIDADES."'" .')"';
		$cadenaXML.=' imagenodo="../images/iconos/entidades.gif"';
		$cadenaXML.=' infonodo="'.$nombreentidad.'"';
		$cadenaXML.=' nodoid='.$LITAMBITO_ENTIDADES.'-'.$identidad;
		$cadenaXML.='></ENTIDAD>';
		return($cadenaXML);
} 
?>
