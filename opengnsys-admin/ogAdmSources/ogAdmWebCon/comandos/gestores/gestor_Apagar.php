<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_Apagar.php
// Descripción : 
//		Gestor del comando "Apagar"
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
$cadenaip="";

include_once("../../includes/cuestionaccionescab.php");

$fp = fopen('../'.$fileparam,"r"); 
$parametros= fread ($fp, filesize ("../".$fileparam));
fclose($fp);

$ValorParametros=extrae_parametros($parametros,chr(13),'=');
$identificador=$ValorParametros["identificador"]; 
$nombrefuncion=$ValorParametros["nombrefuncion"]; 
$ejecutor=$ValorParametros["ejecutor"]; 
$cadenaip=$ValorParametros["cadenaip"]; 
$ambito=$ValorParametros["ambito"]; 
$idambito=$ValorParametros["idambito"]; 

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
	echo 'window.parent.resultado_apagar(1)'.chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo 'window.parent.resultado_apagar(0)'.chr(13);
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
// *************************************************************************************************************************************************
function Gestiona($cmd){
	global $ACCION_SINERRORES; // Activa y con algún error
	global $ACCION_INICIADA;
	global $idcentro;
	global $identificador;
	global $nombrefuncion;
	global $ejecutor;
	global $cadenaip;
	global $ambito; 
	global $idambito;
	global $EJECUCION_COMANDO;
	global $PROCESOS;
	global $servidorhidra;
	global $hidraport;

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

	$parametros=$ejecutor;
	$parametros.="nfn=".$nombrefuncion.chr(13);
	$parametros.="iph=".$cadenaip.chr(13);
	$cmd->ParamSetValor("@parametros",$parametros);

	return(CuestionAcciones($cmd,$shidra,$parametros));
}
?>