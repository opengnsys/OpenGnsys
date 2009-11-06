<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_RestaurarImagenAula.php
// Descripción : 
//		Gestor del comando "RestaurarImagenAula"
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
$pathrmb="";

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
			$perfiles=$ValorParametros["perfiles"]; 
			$pathrmb=$ValorParametros["pathrmb"]; 
			$resul=false;
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
	echo 'window.parent.resultado_RestaurarImagenAula(1)'.chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo 'window.parent.resultado_RestaurarImagenAula(0)'.chr(13);
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
// *************************************************************************************************************************************************
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
	global $cadenaip;
	global $identificador;
	global $nombrefuncion;
	global $ejecutor;
	global $tipotrama; 
	global $ambito; 
	global $idambito;
	global $perfiles;
	global $pathrmb;
	global $EJECUCION_COMANDO;
	global $PROCESOS;
	global $servidorhidra;
	global $hidraport;

	$swvez=true;
	$auxsplit=split(";",$perfiles); // Toma las distintas particiones con sus perfiles
	$auxpsplit=split(";",$pathrmb); // Toma los distintas path de imagens
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
			$swresimg=$dualperfil[1];
			$idimagen=$dualperfil[2];
			$idperfilhard=$dualperfil[3];
			$idperfilsoft=$dualperfil[4];
			$tipopar=$dualperfil[5];
			$nemonico=$dualperfil[6];
			$parametros=$ejecutor;
			$parametros.="nfn=".$nombrefuncion.chr(13);
			$parametros.="par=".$particion.chr(13);
			$parametros.="idi=".$idimagen.chr(13);
			$parametros.="ifs=".$idperfilsoft.chr(13);
			$parametros.="ifh=".$idperfilhard.chr(13);
			$parametros.="nem=".$nemonico.chr(13);
			$parametros.="idc=".$idcentro.chr(13);
			$parametros.="swr=".$swresimg.chr(13);
			$parametros.="icr=".CuestionIncrementales($cmd,$idperfilhard,$idperfilsoft,$idimagen).chr(13);;
			$parametros.="tpa=".$tipopar.chr(13);
			$parametros.="pth=".$auxpsplit[$j].chr(13);
			$parametros.="iph=".$cadenaip.chr(13);
			$cmd->ParamSetValor("@parametros",$parametros);
			if(!CuestionAcciones($cmd,$shidra,$parametros)) return(false);
	}
	return(true);
}
//________________________________________________________________________________________________________
//	Comprueba que la imagen no tiene incrementales o si la tiene que existen para el perfil hardware del ordenador
//________________________________________________________________________________________________________
function CuestionIncrementales($cmd,$idperfilhard,$idperfilsoft,$idimagen){
	$wrs=new Recordset; 
	$cmd->texto=" SELECT idsoftincremental FROM imagenes_softincremental WHERE idimagen=".$idimagen;
	$wrs->Comando=&$cmd; 
	if (!$wrs->Abrir()) return(""); // Error al abrir recordset
	$strInc="";
	while (!$wrs->EOF){
		$strInc.=$wrs->campos["idsoftincremental"].";";
		$wrs->Siguiente();
	}
	return($strInc);
}
?>