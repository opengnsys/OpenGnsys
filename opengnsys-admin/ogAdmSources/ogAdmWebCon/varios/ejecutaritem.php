<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Copyright 200-2005 José Manuel Alonso. Todos los derechos reservados.
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
include_once("../includes/TomaDato.php");
//________________________________________________________________________________________________________
$ipordenador="0.0.0.0";
$idtipoaccion="0";

if (isset($_GET["iph"]))	$ipordenador=$_GET["iph"]; 
if (isset($_GET["idt"]))	$idtipoaccion=$_GET["idt"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2');  // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
$rs=new Recordset; 
$cmd->texto="SELECT acciones_menus.tipoaccion, acciones_menus.idtipoaccion 
			FROM acciones_menus 
			WHERE acciones_menus.idaccionmenu=".$idt;
$rs->Comando=&$cmd; 
$resul=$rs->Abrir();
if (!$rs->Abrir()) die("NO SE HA PODIDO RECUEPARA EL ITEM PARA SER EJECUTADO";
if ($rs->EOF()) die("EL ITEM PARA SER EJECUTADO NO EXISTE";
	
$tipoaccion=$rs->campos["tipoaccion"]; 
switch($tipoaccion){
	case $EJECUCION_PROCEDIMIENTO :
		$ambito=$AMBITO_ORDENADORES;
		$idambito=TomaDato($cmd,$idcentro,'ordenadores',$iph,'ip','idordenador');
		$idprocedimiento=$idt;
		$wurl="../gestores/gestor_ejecutarprocedimientos.php";
		$wurl.="?ambito=".$ambito."&idambito=".$idambito."&idprocedimiento=".$idprocedimiento;
		Header('Location: '.$wurl);  // Ejecución procedimiento
		break;
	case EJECUCION_TAREA :
		$idtrabajo=$idt;
		$wurl="../gestores/gestor_tareas.php?opcion=".$op_ejecucion."&idtarea="+idt;
		Header('Location: '.$wurl);  // Ejecución procedimiento
		break;
	case EJECUCION_TRABAJO :
		$idtrabajo=$idt;
		$wurl="../gestores/gestor_trabajos.php?opcion=".$op_ejecucion."&idtrabajo="+idt;
		Header('Location: '.$wurl);  // Ejecución procedimiento
		break;
}
die("HA HABIDO ALGÚN ERROR AL PROCESAR EL ITEM";
?>
