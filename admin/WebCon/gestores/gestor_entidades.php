<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
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

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"];
if (isset($_GET["iduniversidad"])) $iduniversidad=$_GET["iduniversidad"];
if (isset($_GET["identidad"])) $identidad=$_GET["identidad"];
if (isset($_GET["identificador"])) $identidad=$_GET["identificador"];
if (isset($_GET["nombreentidad"])) $nombreentidad=$_GET["nombreentidad"];
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
echo '<p><span id="arbol_nodo">'.$tablanodo.'</span></p>';
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo 'var oHTML'.chr(13);
	echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
	echo 'o=cTBODY.item(1);'.chr(13);
	if ($opcion==$op_alta )
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$identidad.",o.innerHTML);";
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$nombreentidad."');";
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$identidad.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
/*________________________________________________________________________________________________________
	Inserta, modifica o elimina datos en la tabla entidades
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global $identidad;
	global $nombreentidad;
	global $comentarios;
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
	
	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO entidades(nombreentidad,comentarios,iduniversidad,grupoid) VALUES (@nombreentidad,@comentarios,@iduniversidad,@grupoid)";
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
			$cmd->texto="UPDATE entidades SET nombreentidad=@nombreentidad,comentarios=@comentarios WHERE identidad=@identidad";
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