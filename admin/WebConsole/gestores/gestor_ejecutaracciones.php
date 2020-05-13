<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Mayo-2005
// Nombre del fichero: gestor_procedimientos.php
// Descripción :
//		Gestiona la ejecución de procedimientos
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../clases/XmlPhp.php");
include_once("../clases/ArbolVistaXML.php");
include_once("../includes/CreaComando.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/RecopilaIpesMacs.php");
include_once("../includes/restfunctions.php");
//________________________________________________________________________________________________________

function now_params()
{
	$year = intval(date('Y')) - 2010;
	$month = intval(date('m')) - 1;
	$day = intval(date('j')) - 1;
	$hour = intval(date('g'));
	$am_pm = date('a');
	$minute = intval(date('i'));

	$params['map_year'] = 1 << $year;
	$params['map_month'] = 1 << $month;
	$params['map_day'] = 1 << $day;
	$params['map_hour'] = 1 << $hour;
	$params['map_am_pm'] = strcmp($am_pm, 'am') ? 1 : 0;
	$params['map_minute'] = $minute;

	return $params;
}

define('OG_CMD_ID_WAKEUP', 1);

$opcion=0; // Inicializa parametros

$idprocedimiento=0;
$idtarea=0;
$ambito=0;
$idambito=0;
$swc=0; // switch de cliente, esta pagina la llama el cliente a través del browser

if (isset($_POST["opcion"])) $opcion=$_POST["opcion"];
if (isset($_POST["idprocedimiento"]))	$idprocedimiento=$_POST["idprocedimiento"];
if (isset($_POST["descriprocedimiento"]))	$descriprocedimiento=$_POST["descriprocedimiento"];
if (isset($_POST["ambito"])) $ambito=$_POST["ambito"];
if (isset($_POST["idambito"])) $idambito=$_POST["idambito"];
if (isset($_POST["idtarea"]))	$idtarea=$_POST["idtarea"];
if (isset($_POST["descritarea"]))	$descritarea=$_POST["descritarea"];

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"];
if (isset($_GET["idprocedimiento"])) $idprocedimiento=$_GET["idprocedimiento"];
if (isset($_GET["descriprocedimiento"]))$descriprocedimiento=$_GET["descriprocedimiento"];
if (isset($_GET["ambito"])) $ambito=$_GET["ambito"];
if (isset($_GET["idambito"])) $idambito=$_GET["idambito"];
if (isset($_GET["idtarea"])) $idtarea=$_GET["idtarea"];
if (isset($_GET["descritarea"])) $descritarea=$_GET["descritarea"];

if (isset($_GET["swc"])) $swc=$_GET["swc"]; // Switch que indica que la página la solicita un cliente a través del browser

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$cadenaid="";
	$cadenaip="";
	$cadenamac="";
	$sesion=0;
	$vez=0;
	if(opcion!=$EJECUCION_TAREA)
		RecopilaIpesMacs($cmd,$ambito,$idambito); // Recopila Ipes del ámbito
	if(opcion!=$EJECUCION_AUTOEXEC){
		//Creación parametros para inserción en tabla acciones
		$sesion=0;
		$cmd->CreaParametro("@tipoaccion",$opcion,1);
		$cmd->CreaParametro("@idtipoaccion",0,1);
		$cmd->CreaParametro("@descriaccion","",0);
		$cmd->CreaParametro("@idordenador",0,1);
		$cmd->CreaParametro("@ip","",0);
		$cmd->CreaParametro("@sesion",$sesion,1);
		$cmd->CreaParametro("@idcomando",0,1);
		$cmd->CreaParametro("@parametros","",0);
		$cmd->CreaParametro("@fechahorareg","",0);
		$cmd->CreaParametro("@fechahorafin","",0);
		$cmd->CreaParametro("@estado",$ACCION_INICIADA,1);
		$cmd->CreaParametro("@resultado",$ACCION_SINRESULTADO,1);
		$cmd->CreaParametro("@descrinotificacion","",0);
		$cmd->CreaParametro("@idprocedimiento",0,1);
		$cmd->CreaParametro("@idtarea",0,1);
		$cmd->CreaParametro("@idcentro",$idcentro,1);
		$cmd->CreaParametro("@ambito",0,1);
		$cmd->CreaParametro("@idambito",0,1);
		$cmd->CreaParametro("@restrambito","",0);
	}
	switch($opcion){
		case $EJECUCION_AUTOEXEC:
			$resul=actualizaAutoexec($idprocedimiento);
			$literal="resultado_gestion_procedimiento";
			break;
		case $EJECUCION_PROCEDIMIENTO:
			$cmd->ParamSetValor("@idtipoaccion",$idprocedimiento);
			$cmd->ParamSetValor("@descriaccion",$descriprocedimiento);
			$resul=ejecucionProcedimiento($idprocedimiento,$ambito,$idambito);
			$literal="resultado_gestion_procedimiento";
			break;
		case $EJECUCION_TAREA:
			$cmd->ParamSetValor("@idtipoaccion",$idtarea);
			$cmd->ParamSetValor("@descriaccion",$descritarea);
			$cmd->ParamSetValor("@idtarea",$idtarea);
			$resul=run_task($idtarea);
			$literal="resultado_ejecutar_tareas";
			break;
	}
	$cmd->Conexion->Cerrar();
}
if ($resul){
	if(empty($swc)){
		echo $literal."(1,'".$cmd->DescripUltimoError()."');".chr(13);
	}
	else{
		echo '<SCRIPT language="javascript">'.chr(13);
		echo 'alert("El item se ha ejecutado correctamente");'.chr(13);
		echo 'var wurl="../varios/menucliente.php?iph='.trim($_SESSION["ogCliente"]).'";';
		echo 'history.back();';
		echo '</SCRIPT>';
	}
}
else{
	if(empty($swc)){
		echo $literal."(0,'".$cmd->DescripUltimoError()."')";
	}
	else{
		echo '<SCRIPT language="javascript">'.chr(13);
		echo 'alert("***ATENCIÓN:El item NO se ha podido ejecutar");'.chr(13);
		echo 'var wurl="../varios/menucliente.php?iph='.trim($_SESSION["ogCliente"]).'";';
		echo 'location.href=wurl;';
		echo '</SCRIPT>';
	}
}
//********************************************************************************************************
//
// Incorpora un procedimiento como autoexec
//________________________________________________________________________________________________________
function actualizaAutoexec($idprocedimiento)
{
	global $cadenaid;
	global $cmd;

	$cmd->texto="UPDATE ordenadores SET idproautoexec=".$idprocedimiento." WHERE idordenador IN (".$cadenaid.")";
	$resul=$cmd->Ejecutar();
	return(resul);
}
//________________________________________recorreProcedimientos________________________________________________________________
//
// Ejecuta un procedimiento: lo registra en acciones y lo envía por la red
//________________________________________________________________________________________________________
function ejecucionProcedimiento($idprocedimiento,$ambito,$idambito)
{
	return(recorreProcedimientos($idprocedimiento,$ambito,$idambito));						 
}
//________________________________________________________________________________________________________
function recorreProcedimientos($idprocedimiento,$ambito,$idambito)
{		
	global $cadenamac;
	global $cadenaip;
	global $sesion;
	global $cmd;

	$wol_params;

	$cmd->texto="SELECT   idcomando,procedimientoid,parametros
			 FROM procedimientos_acciones
			WHERE idprocedimiento=".$idprocedimiento." 
			ORDER BY orden";	

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	while (!$rs->EOF){
		$procedimientoid=$rs->campos["procedimientoid"];
		if($procedimientoid>0){ // Procedimiento recursivo
			if(!recorreProcedimientos($procedimientoid,$ambito,$idambito)){
				return(false);
			}
		}
		else{
			$parametros=$rs->campos["parametros"];
			$idcomando=$rs->campos["idcomando"];
			// Ticket 681: bucle infinito en procedimiento compuesto (J.M. Alonso).
			do{
				$nwsesion=time();       
			}while($sesion==$nwsesion);
			$sesion=$nwsesion;
			$cmd->ParamSetValor("@sesion",$sesion);
			// Fin ticket 681.
			if ($idcomando == OG_CMD_ID_WAKEUP)
				$wol_params = $parametros;
			if(!insertaComando($idcomando,$parametros,$idprocedimiento,$ambito,$idambito)) 
				return(false);	
		}
		$rs->Siguiente();
	}

	if (isset($wol_params)) {
		$atributos = substr(trim($wol_params), -1);
		include("../comandos/gestores/wakeonlan_repo.php");
	}

	return(true);
}
//________________________________________________________________________________________________________
//
//	Registra un procedimiento para un ambito concreto
//________________________________________________________________________________________________________
function insertaComando($idcomando,$parametros,$idprocedimiento,$ambito,$idambito)
{
	global $EJECUCION_PROCEDIMIENTO;
	global $cadenaid;
	global $cadenaip;
	global $cmd;	
	global $sesion;
	global $vez;

	if($ambito==0){ // Ambito restringido a un subconjuto de ordenadores con formato (idordenador1,idordenador2,etc)
		$cmd->ParamSetValor("@restrambito",$idambito);
		$idambito=0;
	}	
	
	$cmd->ParamSetValor("@idcomando",$idcomando);
	$cmd->ParamSetValor("@idprocedimiento",$idprocedimiento);
	$cmd->ParamSetValor("@parametros",$parametros);
	$cmd->ParamSetValor("@fechahorareg",date("y/m/d H:i:s"));
	$cmd->ParamSetValor("@ambito",$ambito);
	$cmd->ParamSetValor("@idambito",$idambito);

	if(strlen($cadenaip)==0) return(true);	

	$auxID=explode(",",$cadenaid);
	$auxIP=explode(";",$cadenaip);

	for ($i=0;$i<sizeof($auxID);$i++){
		$cmd->ParamSetValor("@idordenador",$auxID[$i]);
		$cmd->ParamSetValor("@ip",$auxIP[$i]);
		$cmd->texto="INSERT INTO acciones (idordenador,tipoaccion,idtipoaccion,descriaccion,ip,sesion,idcomando,parametros,fechahorareg,estado,resultado,ambito,idambito,restrambito,idprocedimiento,idtarea,idcentro)
					VALUES (@idordenador,@tipoaccion,@idtipoaccion,@descriaccion,@ip,@sesion,@idcomando,@parametros,@fechahorareg,@estado,@resultado,@ambito,@idambito,@restrambito,@idprocedimiento,@idtarea,@idcentro)";
		$resul=$cmd->Ejecutar();
		//echo $cmd->texto;
		if(!$resul) return(false);
		if ($i == 0) {
			$sesion = $cmd->Autonumerico();
			$cmd->ParamSetValor("@sesion",$sesion);
		}
	}
	$cmd->texto = "UPDATE acciones SET sesion=@sesion ".
		      "WHERE idaccion = @sesion";
	$resul=$cmd->Ejecutar();
	if (resul) {
		$when = now_params();
		$resul = create_schedule(strval($sesion), $EJECUCION_PROCEDIMIENTO, "",
					 $when['map_year'], $when['map_month'],
					 0, 0, $when['map_day'],
					 $when['map_hour'], $when['map_am_pm'],
					 $when['map_minute']);
	}
	return(true);
}

