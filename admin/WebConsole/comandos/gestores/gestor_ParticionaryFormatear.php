<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_ParticionaryFormatear.php
// Descripción : 
//		Gestor del comando "ParticionaryFormatear"
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
$particiones="";
$parametros="";
if (isset($_GET["parametros"]))	$parametros=$_GET["parametros"]; 

include_once("../../includes/cuestionaccionescab.php");

$resul=false;
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if ($cmd){
		$auxsplit=split("\t",$parametros);
		$numpar=sizeof($auxsplit);
		for($j=0;$j<$numpar-1;$j++){
			$ValorParametros=extrae_parametros($auxsplit[$j],chr(13),'=');
			$identificador=$ValorParametros["identificador"]; 
			$nombrefuncion=$ValorParametros["nombrefuncion"]; 
			$ejecutor=$ValorParametros["ejecutor"]; 
			$tipotrama=$ValorParametros["tipotrama"]; 
			$ambito=$ValorParametros["ambito"]; 
			$idambito=$ValorParametros["idambito"]; 
			$cadenaip=$ValorParametros["cadenaip"]; 
			$particiones=$ValorParametros["particiones"]; 
			$resul=false;
			$idaula=$idambito;
			$resul=Gestiona($cmd);
			if(!$resul) break;
		}
}
$cmd->Conexion->Cerrar();
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
<BODY>
<?
if ($resul){
	echo '<SCRIPT language="javascript">';
	echo 'window.parent.resultado_ParticionaryFormatear(1)'.chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo 'window.parent.resultado_ParticionaryFormatear(0)'.chr(13);
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
	global $idaula;
	global $cadenaip;
	global $identificador;
	global $nombrefuncion;
	global $ejecutor;
	global $tipotrama; 
	global $ambito; 
	global $idambito;
	global $particiones;
	global $EJECUCION_COMANDO;
	global $PROCESOS;
	global $servidorhidra;
	global $hidraport;
	global $tbTiposParticiones;

	$swvez=true;

	$auxsplit=split("\n",$particiones); // Toma las distintas particiones con sus particiones
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
	$parampar="";
	$lparampar="";
	$hdclean="";
	$isizepart=0;
	for($j=0;$j<sizeof($auxsplit)-1;$j++){
			$cuadruparticion=split(";",$auxsplit[$j]);
			$particion=$cuadruparticion[0];
			$tipopart=$cuadruparticion[1];
			$nemopar=$tbTiposParticiones[$tipopart];
			$sizepart=$cuadruparticion[2];
			if($particion>4)
				$isizepart+=(int)($sizepart);
			$accion=$cuadruparticion[3];
			if($accion==2) $nemopar="H".$nemopar; // Particion oculta
			if($particion<5)
				$parampar.=$nemopar.":".$sizepart." ";
			else
				$lparampar.=$nemopar.":".$sizepart." ";
			if($accion==1) $hdclean.=$particion.";"; // Formatear la partición
	}
	if($isizepart>0) // Existen particiones extendidas
		$parampar.="EXT:".$isizepart." ";
	$parampar=substr($parampar,0,strlen($parampar)-1); // Quita el espacion final
	if(strlen($lparampar)>0)
		$lparampar=substr($lparampar,0,strlen($lparampar)-1); // Quita el espacion final
	$hdclean=substr($hdclean,0,strlen($hdclean)-1); // Quita la coma final
	//________________________________________________________________________________________________________
	$parametros=$ejecutor;
	$parametros.="nfn=".$nombrefuncion.chr(13);
	$parametros.="ppa=".$parampar.chr(13);
	$parametros.="lpa=".$lparampar.chr(13);
	$parametros.="hdc=".$hdclean.chr(13);
	$parametros.="iph=".$cadenaip.chr(13);

	$cmd->ParamSetValor("@parametros",$parametros);
	return(CuestionAcciones($cmd,$shidra,$parametros));
}
?>