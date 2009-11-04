<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
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

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros

if (isset($_GET["idreserva"])) $idreserva=$_GET["idreserva"];
if (isset($_GET["descripcion"])) $descripcion=$_GET["descripcion"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"];
if (isset($_GET["solicitante"])) $solicitante=$_GET["solicitante"]; 
if (isset($_GET["email"])) $email=$_GET["email"]; 
if (isset($_GET["idestatus"])) $idestatus=$_GET["idestatus"]; 
if (isset($_GET["idaula"])) $idaula=$_GET["idaula"]; 
if (isset($_GET["idimagen"])) $idimagen=$_GET["idimagen"]; 
if (isset($_GET["idtarea"])) $idtarea=$_GET["idtarea"]; 
if (isset($_GET["idtrabajo"])) $idtrabajo=$_GET["idtrabajo"]; 
if (isset($_GET["estado"])) $estado=$_GET["estado"]; 
if (isset($_GET["comentarios"])) $comentarios=$_GET["comentarios"]; 
if (isset($_GET["identificador"])) $idreserva=$_GET["identificador"];

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
echo '<p><span id="arbol_nodo">'.$tablanodo.'</span></p>';
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo 'var oHTML'.chr(13);
	echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
	echo 'o=cTBODY.item(1);'.chr(13);
	if ($opcion==$op_alta )
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idreserva.",o.innerHTML);";
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ','".$descripcion."');";
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idreserva.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
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
?>