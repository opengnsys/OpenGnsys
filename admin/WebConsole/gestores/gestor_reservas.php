<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: gestor_reservas.php
// Descripción :
//		Gestiona el mantenimiento de la tabla de reservas
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("./relaciones/reservas_eliminacion.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros
 
$idreserva=0; 
$descripcion="";
$grupoid=0; 
$solicitante="";
$email="";
$idestatus=0;
$idaula=0;
$idimagen=0;
$idtarea=0;
$idtrabajo=0;
$estado=0;
$comentarios="";

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"]; // Recoge parametros

if (isset($_POST["idreserva"])) $idreserva=$_POST["idreserva"];
if (isset($_POST["descripcion"])) $descripcion=$_POST["descripcion"]; 
if (isset($_POST["grupoid"])) $grupoid=$_POST["grupoid"];
if (isset($_POST["solicitante"])) $solicitante=$_POST["solicitante"]; 
if (isset($_POST["email"])) $email=$_POST["email"]; 
if (isset($_POST["idestatus"])) $idestatus=$_POST["idestatus"]; 
if (isset($_POST["idaula"])) $idaula=$_POST["idaula"]; 
if (isset($_POST["idimagen"])) $idimagen=$_POST["idimagen"]; 
if (isset($_POST["idtarea"])) $idtarea=$_POST["idtarea"]; 
if (isset($_POST["idtrabajo"])) $idtrabajo=$_POST["idtrabajo"]; 
if (isset($_POST["estado"])) $estado=$_POST["estado"]; 
if (isset($_POST["comentarios"])) $comentarios=$_POST["comentarios"]; 
if (isset($_POST["identificador"])) $idreserva=$_POST["identificador"];

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
	echo '	<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>';
	echo '	<SCRIPT language="javascript" src="../jscripts/propiedades_reservas.js"></SCRIPT>';
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
		$literal="resultado_insertar_reservas";
		break;
	case $op_modificacion:
		$literal="resultado_modificar_reservas";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_reservas";
		break;
	case $op_movida :
		$literal="resultado_mover";
		break;
	default:
		break;
}
if ($resul){
	if ($opcion==$op_alta )
		echo $literal."(1,'".$cmd->DescripUltimoError()." ',".$idreserva.",o.innerHTML);".chr(13);
	else
		echo $literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');".chr(13);
}
else
	echo $literal."(0,'".$cmd->DescripUltimoError()."',".$idreserva.")";

if($opcion!=$op_movida){
	echo '	</SCRIPT>';
	echo '</BODY>	';
	echo '</HTML>';	
}
/**************************************************************************************************************************************************
	Inserta, modifica o elimina datos en la tabla reservas
________________________________________________________________________________________________________*/
function Gestiona(){
	global	$cmd;
	global	$opcion;

	global	$idcentro;
	global	$idreserva;
	global	$descripcion;
	global	$grupoid;
	global	$solicitante;
	global	$email;
	global	$idestatus;
	global	$idaula;
	global	$idimagen;
	global	$idtarea;
	global	$idtrabajo;
	global	$estado;
	global	$comentarios;

	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;
	global	$op_movida;

	global	$tablanodo;

	$cmd->CreaParametro("@idcentro",$idcentro,1);

	$cmd->CreaParametro("@idreserva",$idreserva,1);
	$cmd->CreaParametro("@descripcion",$descripcion,0);
	$cmd->CreaParametro("@solicitante",$solicitante,0);
	$cmd->CreaParametro("@email",$email,0);
	$cmd->CreaParametro("@idestatus",$idestatus,1);
	$cmd->CreaParametro("@idaula",$idaula,1);
	$cmd->CreaParametro("@idimagen",$idimagen,1);
	$cmd->CreaParametro("@idtarea",$idtarea,1);
	$cmd->CreaParametro("@idtrabajo",$idtrabajo,1);
	$cmd->CreaParametro("@estado",$estado,1);
	$cmd->CreaParametro("@grupoid",$grupoid,1);
	$cmd->CreaParametro("@comentarios",$comentarios,0);

	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO reservas (descripcion,solicitante,email,idestatus,idaula,idimagen,idtarea,idtrabajo,estado,comentarios,idcentro,grupoid) VALUES (@descripcion,@solicitante,@email,@idestatus,@idaula,@idimagen,@idtarea,@idtrabajo,@estado,@comentarios,@idcentro,@grupoid)";
			$resul=$cmd->Ejecutar();
			if ($resul){ // Crea una tabla nodo para devolver a la página que llamó ésta
				$idreserva=$cmd->Autonumerico();
				$arbolXML=SubarbolXML_reservas($idreserva,$descripcion,$estado);
				$baseurlimg="../images/signos"; // Url de las reservas de signo
				$clasedefault="texto_arbol"; // Hoja de estilo (Clase por defecto) del árbol
				$arbol=new ArbolVistaXML($arbolXML,0,$baseurlimg,$clasedefault);
				$tablanodo=$arbol->CreaArbolVistaXML();
			}
			break;
		case $op_modificacion:
			$cmd->texto="UPDATE reservas SET descripcion=@descripcion,solicitante=@solicitante, email=@email,idestatus=@idestatus,idaula=@idaula,idimagen=@idimagen,idtarea=@idtarea,idtrabajo=@idtrabajo,estado=@estado,comentarios=@comentarios WHERE idreserva=@idreserva";
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			$resul=EliminaReservas($cmd,$idreserva,"idreserva");// Eliminación en cascada
			break;
		case $op_movida :
			$cmd->texto="UPDATE reservas SET  grupoid=@grupoid WHERE idreserva=@idreserva";
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
function SubarbolXML_reservas($idreserva,$descripcion,$estado){
		global 	$LITAMBITO_RESERVAS;
		global 	$RESERVA_CONFIRMADA;
		global 	$RESERVA_PENDIENTE;
		global 	$RESERVA_DENEGADA;

		$tbimg[$RESERVA_CONFIRMADA]='../images/iconos/confirmadas.gif';
		$tbimg[$RESERVA_PENDIENTE]='../images/iconos/pendientes.gif';
		$tbimg[$RESERVA_DENEGADA]='../images/iconos/denegadas.gif';

		$cadenaXML='<RESERVA';
		// Atributos
		$cadenaXML.=' imagenodo="'.$tbimg[$estado].'"';
		$cadenaXML.=' infonodo="'.$descripcion.'"';
		$cadenaXML.=' clickcontextualnodo="menu_contextual(this,' ."'flo_".$LITAMBITO_RESERVAS."'" .')"';
		$cadenaXML.=' nodoid='.$LITAMBITO_RESERVAS.'-'.$idreserva;
		$cadenaXML.='>';
		$cadenaXML.='</RESERVA>';
		return($cadenaXML);
}

