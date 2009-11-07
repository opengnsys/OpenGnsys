<?
// *************************************************************************************************************************************************
// Aplicaci�n WEB: ogAdmWebCon
// Autor: Jos� Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaci�n: A�o 2003-2004
// Fecha �ltima modificaci�n: Marzo-2005
// Nombre del fichero: gestor_accionmenu.php
// Descripci�n :
//		Gestiona el mantenimiento de la tabla de acciones_menus
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/opciones.php");
//________________________________________________________________________________________________________
$opcion=0; // Inicializa parametros
$idtipoaccion=0; 
$idmenu=0; 
$tipoaccion=0; 
$tipoitem=0; 
$idurlimg=0; 
$descripitem=""; 
$orden=0; 
$idaccionmenu=0; 

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idtipoaccion"])) $idtipoaccion=$_GET["idtipoaccion"];
if (isset($_GET["idmenu"])) $idmenu=$_GET["idmenu"];
if (isset($_GET["tipoaccion"])) $tipoaccion=$_GET["tipoaccion"];
if (isset($_GET["tipoitem"])) $tipoitem=$_GET["tipoitem"];
if (isset($_GET["idurlimg"])) $idurlimg=$_GET["idurlimg"];
if (isset($_GET["descripitem"])) $descripitem=$_GET["descripitem"];
if (isset($_GET["orden"])) $orden=$_GET["orden"];
if (isset($_GET["idaccionmenu"])) $idaccionmenu=$_GET["idaccionmenu"];

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona();
	$cmd->Conexion->Cerrar();
}
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8"></HEAD>
<BODY>
<?
$literal="";
switch($opcion){
	case $op_alta :
		$literal="resultado_insertar_accionmenu";
		break;
	case $op_modificacion :
		$literal="resultado_modificar_accionmenu";
		break;
	case $op_eliminacion :
		$literal="resultado_eliminar_accionmenu";
		break;
	default:
		break;
}
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	if ($opcion==$op_alta )
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()."');".chr(13);
	else
		echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()." ',".$idtipoaccion.",".$idmenu.");".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."',".$idmenu.")";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
// *************************************************************************************************************************************************
function Gestiona(){
	global	$cmd;
	global	$opcion;
	global	$idtipoaccion;
	global	$idmenu;
	global	$tipoaccion;
	global	$tipoitem;
	global  $idurlimg;
	global  $descripitem;
	global  $orden;
	global  $idaccionmenu;
	global	$op_alta;
	global	$op_modificacion;
	global	$op_eliminacion;

	$cmd->CreaParametro("@idtipoaccion",$idtipoaccion,1);
	$cmd->CreaParametro("@idmenu",$idmenu,1);
	$cmd->CreaParametro("@tipoaccion",$tipoaccion,1);
	$cmd->CreaParametro("@tipoitem",$tipoitem,1);
	$cmd->CreaParametro("@idurlimg",$idurlimg,1);
	$cmd->CreaParametro("@descripitem",$descripitem,0);
	$cmd->CreaParametro("@orden",$orden,1);
	
	switch($opcion){
		case $op_alta :
			$cmd->texto="INSERT INTO acciones_menus (idtipoaccion,idmenu,tipoaccion,tipoitem,idurlimg,descripitem,orden) VALUES (@idtipoaccion,@idmenu,@tipoaccion,@tipoitem,@idurlimg,@descripitem,@orden)";
			$resul=$cmd->Ejecutar();
			break;
		case $op_modificacion :
			$cmd->texto='UPDATE acciones_menus set tipoitem=@tipoitem,idurlimg=@idurlimg,descripitem=@descripitem,orden=@orden WHERE idtipoaccion='.$idtipoaccion.' AND idmenu='.$idmenu.' AND tipoaccion='.$tipoaccion;;
			$resul=$cmd->Ejecutar();
			break;
		case $op_eliminacion :
			if(!empty($idaccionmenu))
						$cmd->texto='DELETE FROM acciones_menus WHERE idaccionmenu='.$idaccionmenu;
			else
						$cmd->texto='DELETE FROM acciones_menus WHERE idtipoaccion='.$idtipoaccion.' AND idmenu='.$idmenu.' AND tipoaccion='.$tipoaccion;
			$resul=$cmd->Ejecutar();
			break;
		default:
			break;
	}
	return($resul);
}
?>