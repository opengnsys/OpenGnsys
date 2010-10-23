<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009
// Fecha Última modificación: Octubre-2009
// Nombre del fichero: menumliente.php
// Descripción :
//		Pagina del menu del cliente. Éste la solicita a través de su browser local
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/opciones.php");
include_once("../includes/TomaDato.php");
//________________________________________________________________________________________________________
$iph="0.0.0.0";
$idt="0";

if (isset($_GET["iph"])) $iph=$_GET["iph"]; 
if (isset($_GET["idt"])) $idt=$_GET["idt"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2');  // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
$rs=new Recordset; 
$cmd->texto="SELECT acciones_menus.tipoaccion, acciones_menus.idtipoaccion,
			procedimientos.descripcion as procedimiento, tareas.descripcion as tarea 
 			FROM acciones_menus 
			LEFT OUTER JOIN procedimientos ON procedimientos.idprocedimiento=acciones_menus.idtipoaccion
			LEFT OUTER JOIN tareas ON tareas.idtarea=acciones_menus.idtipoaccion
			WHERE acciones_menus.idaccionmenu=".$idt;
			
$rs->Comando=&$cmd; 
$resul=$rs->Abrir();
if (!$rs->Abrir()) die("NO SE HA PODIDO RECUPERAR EL ITEM PARA SER EJECUTADO");
if ($rs->EOF) die("EL ITEM PARA SER EJECUTADO NO EXISTE");
	
$tipoaccion=$rs->campos["tipoaccion"]; 
$idtipoaccion=$rs->campos["idtipoaccion"];
 
switch($tipoaccion){
	case $EJECUCION_PROCEDIMIENTO :
		$ambito=$AMBITO_ORDENADORES;
		$idambito=TomaDato($cmd,0,'ordenadores',$iph,'ip','idordenador',0);
		$wurl="../gestores/gestor_ejecutaracciones.php";
		$prm="?swc=1&opcion=".$EJECUCION_PROCEDIMIENTO."&ambito=".$ambito."&idambito=".$idambito;
		$prm.="&idprocedimiento=".$idtipoaccion."&descriprocedimiento=".UrlEncode($rs->campos["procedimiento"]);
		Header('Location: '.$wurl.$prm);  // Ejecución procedimiento
		break;
	case $EJECUCION_TAREA :
		$wurl="../gestores/gestor_ejecutaracciones.php";
		$prm="?swc=1&opcion=".$EJECUCION_TAREA;
		$prm.="&idtarea=".$idtipoaccion."&descritarea=".UrlEncode($rs->campos["tarea"]);
		Header('Location: '.$wurl.$prm);  // Ejecución procedimiento
		break;
}
die("HA HABIDO ALGÚN ERROR AL PROCESAR EL ITEM");
?>
