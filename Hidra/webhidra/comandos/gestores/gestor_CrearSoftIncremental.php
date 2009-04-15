<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_SoftIncremental.php
// Descripción : 
//		Gestor del comando "SoftIncremental"
// *************************************************************************************************************************************************
include_once("../../includes/ctrlacc.php");
include_once("../../clases/AdoPhp.php");
include_once("../../clases/SockHidra.php");
include_once("../../includes/constantes.php");
include_once("../../includes/comunes.php");
include_once("../../includes/cuestionacciones.php");
include_once("../../includes/CreaComando.php");
//________________________________________________________________________________________________________
$identificador=0;
$nombrefuncion="";
$ejecutor="";
$tipotrama=""; 
$ambito=0; 
$idambito=0;
$cadenaip="";
$perfiles="";
$idperfilhard=0;

if (isset($_GET["identificador"]))	$identificador=$_GET["identificador"]; 
if (isset($_GET["nombrefuncion"]))	$nombrefuncion=$_GET["nombrefuncion"]; 
if (isset($_GET["ejecutor"]))	$ejecutor=$_GET["ejecutor"]; 
if (isset($_GET["tipotrama"]))	$tipotrama=$_GET["tipotrama"]; 
if (isset($_GET["ambito"]))	$ambito=$_GET["ambito"]; 
if (isset($_GET["idambito"]))	$idambito=$_GET["idambito"]; 
if (isset($_GET["idperfilhard"])) $idperfilhard=$_GET["idperfilhard"]; 
if (isset($_GET["cadenaip"]))	$cadenaip=$_GET["cadenaip"]; 
if (isset($_GET["perfiles"]))	$perfiles=$_GET["perfiles"]; 

include_once("../../includes/cuestionaccionescab.php");

$idordenador=$idambito; 
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=false;
if ($cmd){
	$resul=Gestiona($cmd);
	$cmd->Conexion->Cerrar();
}
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
<BODY>
<?
if ($resul){
	echo '<SCRIPT language="javascript">';
	echo 'window.parent.resultado_crearsoftincremental(1)'.chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo 'window.parent.resultado_crearsoftincremental(0)'.chr(13);
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
// *************************************************************************************************************************************************
//	Devuelve el nemonico de un S.O. incluido en un perfil software 
//		Parametros: 
//		- cmd:Una comando ya operativo (con conexión abierta)  
//		- ips: identificador del perfil software  
//________________________________________________________________________________________________________
function toma_nemonico($cmd,$ips){
	$cmd->texto="SELECT tiposos.nemonico FROM perfilessoft INNER JOIN perfilessoft_softwares ON perfilessoft.idperfilsoft = perfilessoft_softwares.idperfilsoft INNER JOIN softwares ON perfilessoft_softwares.idsoftware = softwares.idsoftware INNER JOIN tiposos ON softwares.idtiposo = tiposos.idtiposo WHERE tiposos.idtiposo > 0 AND perfilessoft.idperfilsoft=".$ips;
	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF)
		return($rs->campos["nemonico"]);
	else
		return("");
}
//________________________________________________________________________________________________________
function Gestiona($cmd){
	global $ACCION_EXITOSA; // Finalizada con exito
	global $ACCION_FALLIDA; // Finalizada con errores
	global $ACCION_TERMINADA; // Finalizada manualmente con indicacion de exito 
	global $ACCION_ABORTADA; // Finalizada manualmente con indicacion de errores 
	global $ACCION_SINERRORES; // Activa y con algún error
	global $ACCION_CONERRORES; // Activa y sin error
	global $ACCION_DETENIDA;
	global $ACCION_INICIADA;
	global $ACCION_FINALIZADA;
	global $idcentro;
	global $idperfilhard; 
	global $cadenaip;
	global $identificador;
	global $nombrefuncion;
	global $ejecutor;
	global $tipotrama; 
	global $ambito; 
	global $idambito;
	global $perfiles;
	global $EJECUCION_COMANDO;
	global $PROCESOS;
	global $servidorhidra;
	global $hidraport;

	$auxsplit=split(";",$perfiles); // Toma las distintas particiones con sus perfiles
	$shidra=new SockHidra($servidorhidra,$hidraport); 
	
	$cmd->CreaParametro("@tipoaccion",$EJECUCION_COMANDO,1);
	$cmd->CreaParametro("@idtipoaccion",$identificador,1);
	$cmd->CreaParametro("@cateaccion",$PROCESOS,1);
	$cmd->CreaParametro("@ambito",$ambito,1);
	$cmd->CreaParametro("@idambito",$idambito,1);
	$cmd->CreaParametro("@fechahorareg",date("y/m/d H:i:s"),0);
	$cmd->CreaParametro("@estado",$ACCION_INICIADA,0);
	$cmd->CreaParametro("@resultado",$ACCION_SINERRORES,0);
	$cmd->CreaParametro("@idcentro",$idcentro,1);
	$cmd->CreaParametro("@parametros","",0);
	$cmd->CreaParametro("@descripcion","",0);
	$cmd->CreaParametro("@idtarea",0,1);
	$cmd->CreaParametro("@idprocedimiento",0,1);
	$cmd->CreaParametro("@idcomando",0,1);
	for($j=0;$j<sizeof($auxsplit)-1;$j++){
			$dualperfil=split("_",$auxsplit[$j]);
			$particion=$dualperfil[0];
			$idperfilsoft=$dualperfil[1];
			$idsoftincremental=$dualperfil[2];
			$nemonico=toma_nemonico($cmd,$idperfilsoft);
			$parametros=$ejecutor;
			$parametros.="nfn=".$nombrefuncion.chr(13);
			$parametros.="par=".$particion.chr(13);
			$parametros.="ifs=".$idperfilsoft.chr(13);
			$parametros.="ifh=".$idperfilhard.chr(13);
			$parametros.="nem=".$nemonico.chr(13);
			$parametros.="icr=".$idsoftincremental.chr(13);
			$parametros.="iph=".$cadenaip.chr(13);
			$cmd->ParamSetValor("@parametros",$parametros);
			if(!CuestionAcciones($cmd,$shidra,$parametros)) return(false);
	}
	return(true);
}
?>