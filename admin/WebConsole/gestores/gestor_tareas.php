<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_tareas.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de tareas
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("./relaciones/tareas_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros

$idtarea=0; 
$descripcion="";
$comentarios="";
$ambito="";
$idambito="";


$grupoid=0; 
$swc=0; // switch de cliente, esta pagina la llama el cliente a través del browser 

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros

if (isset($_POST["idtarea"])) $idtarea=$_POST["idtarea"];
if (isset($_POST["descripcion"])) $descripcion=$_POST["descripcion"]; 
if (isset($_POST["comentarios"])) $comentarios=$_POST["comentarios"]; 
if (isset($_POST["ambito"])) $ambito=$_POST["ambito"]; 
if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
if (isset($_POST["grupoid"])) $grupoid=$_POST["grupoid"];
if (isset($_POST["identificador"])) $idtarea=$_POST["identificador"];
if (isset($_POST["swc"])) $swc=$_POST["swc"]; 

$tablanodo=""; // Arbol para nodos insertados

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}

$literal="";
switch($opcion){
	case $op_alta :
		$literal="resultado_insertar_tareas";
		break;
	case $op_modificacion:
		$literal="resultado_modificar_tareas";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_tareas";
		break;
	case $op_movida :
		$literal="resultado_mover";
		break;
	case $op_ejecucion :
		$literal="resultado_ejecutar_tareas";
		break;
	default:
		break;
}
if($opcion!=$op_movida){
	echo '<HTML>';
	echo '<HEAD>';
	echo '	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">';
	echo '<BODY>';
	echo '	<SCRIPT language="javascript" src="../jscripts/propiedades_tareas.js"></SCRIPT>';
	echo '<p><span style="visibility:hidden" id="arbol_nodo">'.$tablanodo.'</span></p>';
	if ($resul){
		if(empty($swc)){
			echo '<SCRIPT language="javascript">'.chr(13);
			echo 'var oHTML'.chr(13);
			echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
			echo 'o=cTBODY.item(1);'.chr(13);
			if ($opcion==$op_alta )
				echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idtarea.",o.innerHTML);".chr(13);
			else{
				echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');".chr(13);
			}
			echo '</SCRIPT>';
		}
		else{
			echo '<SCRIPT language="javascript">'.chr(13);
			echo 'alert("El item se ha ejecutado correctamente");'.chr(13);
			echo 'location.href="../varios/menucliente.php?iph='.$_SESSION["ogCliente"].'";'.chr(13);
			echo '</SCRIPT>';
		}		
	}
	else{
		if(empty($swc)){
			echo '<SCRIPT language="javascript">';
			echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idtarea.")";
			echo '</SCRIPT>';
		}
		else{
			echo '<SCRIPT language="javascript">'.chr(13);
			echo 'alert("***ATENCIÓN:El item NO se ha podido ejecutar");'.chr(13);
			echo 'location.href="../varios/menucliente.php?iph='.$_SESSION["ogCliente"].'";'.chr(13);
			echo '</SCRIPT>';
		}
	}
	echo '</BODY>';
	echo '</HTML>';	
}
else{
	if ($resul)
				echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');".chr(13);
	else
			echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idtarea.")";
}
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla tareas
________________________________________________________________________________________________________*/
function Gestiona(){
	global $EJECUCION_TAREA;

	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$idtarea;
	global	$descripcion;
	global	$comentarios;
	global	$ambito;
	global	$idambito;	
	global	$grupoid;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;
	global	$tablanodo;
	$resul=false;

	$cmd->CreaParametro("@idcentro",$idcentro,1);
	$cmd->CreaParametro("@idtarea",$idtarea,1);
	$cmd->CreaParametro("@descripcion",$descripcion,0);
	$cmd->CreaParametro("@comentarios",$comentarios,0);
	$cmd->CreaParametro("@ambito",$ambito,1);
	$cmd->CreaParametro("@idambito",$idambito,1);
	$cmd->CreaParametro("@grupoid",$grupoid,1);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO tareas (descripcion,comentarios,ambito,idambito,idcentro,grupoid)
						VALUES (@descripcion,@comentarios,@ambito,@idambito,@idcentro,@grupoid)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idtarea=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_tareas($idtarea,$descripcion,"");
				$baseurlimg="../images/signos"; // Url de las imagenes de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE tareas SET descripcion=@descripcion,comentarios=@comentarios,
							ambito=@ambito,idambito=@idambito
							WHERE idtarea=@idtarea";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaTareas($cmd,$idtarea,"idtarea");	
			break;
		case $op_movida :
			$cmd->texto="UPDATE tareas SET  grupoid=@grupoid WHERE idtarea=@idtarea";
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
function SubarbolXML_tareas($idtarea,$descripcion,$urlimg){
		global $LITAMBITO_TAREAS;
		$cadenaXML='<TAREA';
		// Atributos`
		if	($urlimg!="")
			$cadenaXML.=' imagenodo="'.$urlimg;
		else
			$cadenaXML.=' imagenodo="../images/iconos/tareas.gif"';
		$cadenaXML.=' infonodo="'.$descripcion;
		$cadenaXML.=' nodoid='.$LITAMBITO_TAREAS.'-'.$idtarea;
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_TAREAS."'" .')"';
		$cadenaXML.='>';
		$cadenaXML.='</TAREA>';
		return($cadenaXML);
}
//________________________________________________________________________________________________________

