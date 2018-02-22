<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_imagenes.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de imagenes
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("./relaciones/imagenes_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros
 
$idimagen=0; 
$nombreca="";
$ruta="";
$descripcion="";
$grupoid=0; 
$idperfilsoft=0;
$comentarios="";
$inremotepc=false;
$numpar=0;
$codpar=0;
$idrepositorio=0;
$imagenid=0;
$tipoimg=0;
$litamb="";

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros

if (isset($_POST["idimagen"])) $idimagen=$_POST["idimagen"];
if (isset($_POST["nombreca"])) $nombreca=$_POST["nombreca"]; 
if (isset($_POST["ruta"])) $ruta=$_POST["ruta"]; 
if (isset($_POST["descripcion"])) $descripcion=$_POST["descripcion"]; 
if (isset($_POST["grupoid"])) $grupoid=$_POST["grupoid"];
if (isset($_POST["idperfilsoft"])) $idperfilsoft=$_POST["idperfilsoft"]; 
if (isset($_POST["comentarios"])) $comentarios=$_POST["comentarios"]; 
if (isset($_POST["inremotepc"])) $inremotepc=$_POST["inremotepc"]; 
if (isset($_POST["identificador"])) $idimagen=$_POST["identificador"];
if (isset($_POST["numpar"])) $numpar=$_POST["numpar"]; 
if (isset($_POST["codpar"])) $codpar=$_POST["codpar"]; 
if (isset($_POST["idrepositorio"])) $idrepositorio=$_POST["idrepositorio"]; 
if (isset($_POST["imagenid"])) $imagenid=$_POST["imagenid"]; 
if (isset($_POST["tipoimg"])) $tipoimg=$_POST["tipoimg"]; 
if (isset($_POST["litamb"])) $litamb=$_POST["litamb"]; 

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
	echo '	<SCRIPT language="javascript" src="../jscripts/propiedades_imagenes.js"></SCRIPT>';
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
		$literal="resultado_insertar_imagenes";
		break;
	case $op_modificacion:
		$literal="resultado_modificar_imagenes";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_imagenes";
		break;
	case $op_movida :
		$literal="resultado_mover";
		break;
	default:
		break;
}
if ($resul){
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idimagen.",o.innerHTML);".chr(13);
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');".chr(13);
}
else
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idimagen.")";

if($opcion!=$op_movida){
	echo '	</SCRIPT>';
	echo '</BODY>	';
	echo '</HTML>';	
}
/*********************************************************************************************************
	Inserta, modifica o elimina datos en la tabla imagenes
/*********************************************************************************************************/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$idimagen;
	global	$nombreca;
	global	$ruta;
	global	$descripcion;
	global	$grupoid;
	global	$comentarios;
	global	$inremotepc;
	global	$numpar;
	global	$codpar;
	global	$idrepositorio;
	global	$imagenid;
	global	$idperfilsoft;
	global	$tipoimg;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;

	global	$tablanodo;

	$cmd->CreaParametro("@idcentro",$idcentro,1);

	$cmd->CreaParametro("@idimagen",$idimagen,1);
	$cmd->CreaParametro("@nombreca",$nombreca,0);
	$cmd->CreaParametro("@ruta",$ruta,0);
	$cmd->CreaParametro("@descripcion",$descripcion,0);
	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@idperfilsoft",$idperfilsoft,1);
	$cmd->CreaParametro("@comentarios",$comentarios,0);
	$cmd->CreaParametro("@inremotepc",$inremotepc,1);
	$cmd->CreaParametro("@numpar",$numpar,1);
	$cmd->CreaParametro("@codpar",$codpar,1);
	$cmd->CreaParametro("@idrepositorio",$idrepositorio,1);
	$cmd->CreaParametro("@imagenid",$imagenid,1);
	$cmd->CreaParametro("@tipo",$tipoimg,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO imagenes
						 (nombreca, ruta, descripcion, idperfilsoft, 
						 comentarios, inremotepc, numpar, codpar,
						 idrepositorio, imagenid, idcentro, grupoid, tipo)
					  VALUES (@nombreca, @ruta, @descripcion, @idperfilsoft,
						 @comentarios, @inremotepc, @numpar, @codpar,
						 @idrepositorio, @imagenid, @idcentro, @grupoid, @tipo)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idimagen=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_imagenes($idimagen,$descripcion);
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE imagenes SET
					    nombreca=@nombreca, ruta=@ruta, descripcion=@descripcion,
					    idperfilsoft=@idperfilsoft, comentarios=@comentarios,
					    inremotepc=@inremotepc, numpar=@numpar,codpar=@codpar,
					    idrepositorio=@idrepositorio, imagenid=@imagenid
				      WHERE idimagen=@idimagen";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaImagenes($cmd,$idimagen,"idimagen");// Eliminación en cascada
			break;
		case $op_movida :
			$cmd->texto="UPDATE imagenes SET  grupoid=@grupoid WHERE idimagen=@idimagen";
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
function SubarbolXML_imagenes($idimagen,$descripcion)
{
		global $litamb;

		$cadenaXML='<IMAGEN';
		// Atributos
		$cadenaXML.=' imagenodo="../images/iconos/imagen.gif"';
		$cadenaXML.=' infonodo="'.$descripcion.'"';
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$litamb."'" .')"';
		$cadenaXML.=' nodoid='.$litamb.'-'.$idimagen;
		$cadenaXML.='>';
		$cadenaXML.='</IMAGEN>';
		return($cadenaXML);
}
?>
