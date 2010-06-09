<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: gestor_ordenadorestandar.php
// Descripción :
//		Gestiona la actualización de los ordenadores de un aula a través de la plantilla
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
//________________________________________________________________________________________________________
$idaula=0;
$modomul=0;
$ipmul="";
$pormul=0;
$velmul=0;
$cache=0;
$idperfilhard=0;
$idservidordhcp=0;
$idservidorrembo=0;
$numorde=0;

if (isset($_GET["idaula"])) $idaula=$_GET["idaula"];
if (isset($_GET["modomul"])) $modomul=$_GET["modomul"];
if (isset($_GET["ipmul"])) $ipmul=$_GET["ipmul"];
if (isset($_GET["pormul"])) $pormul=$_GET["pormul"];
if (isset($_GET["velmul"])) $velmul=$_GET["velmul"];
if (isset($_GET["cache"])) $cache=$_GET["cache"];
if (isset($_GET["idperfilhard"])) $idperfilhard=$_GET["idperfilhard"];
if (isset($_GET["idservidordhcp"])) $idservidordhcp=$_GET["idservidordhcp"];
if (isset($_GET["idservidorrembo"])) $idservidorrembo=$_GET["idservidorrembo"];
if (isset($_GET["numorde"])) $numorde=$_GET["numorde"];

if(empty($cache)) $cache=0;

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
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<BODY>
<?
$literal="resultado_ordenadorestandar";
if ($resul){
	echo '<SCRIPT language="javascript">'.chr(13);
	echo 'var oHTML'.chr(13);
	echo 'var cTBODY=document.getElementsByTagName("TBODY");'.chr(13);
	echo 'o=cTBODY.item(1);'.chr(13);
	echo 'window.parent.'.$literal."(1,'".$cmd->DescripUltimoError()."');".chr(13);
	echo '</SCRIPT>';
}
else{
	echo '<SCRIPT language="javascript">';
	echo "	window.parent.".$literal."(0,'".$cmd->DescripUltimoError()."')";
	echo '</SCRIPT>';
}
?>
</BODY>
</HTML>	
<?
// *************************************************************************************************************************************************
function Gestiona(){
	global	$cmd;
	global $idaula;
	global $modomul;
	global $ipmul;
	global $pormul;
	global $velmul;
	global $cache;
	global $idaula;
	global $idperfilhard;
	global $idservidordhcp;
	global $idservidorrembo;
	global $numorde;

	if($numorde>0){ 
		$auxIP=split("[.]",$ipmul);
		$swipmul=false;
		$litnwipmul="";
		$nwipmul=0;
		if(isset($auxIP[3])){
			$nwipmul=$auxIP[3];
			if(empty($nwipmul)) $nwipmul=0;
			$litnwipmul=$auxIP[0].".".$auxIP[1].".".$auxIP[2].".";
			$swipmul=true;
		}
		$swnom=false;
		if(substr($modomul,strlen($modomul)-1,1)=="$"){
			$swnom=true;
			$modomul=substr($modomul,0,strlen($modomul)-1);
		}
		$cmd->CreaParametro("@grupoid",0,1);
		$cmd->CreaParametro("@idaula",$idaula,1);
		$cmd->CreaParametro("@modomul",$modomul,0);
		$cmd->CreaParametro("@ipmul",$ipmul,0);
		$cmd->CreaParametro("@pormul",$pormul,1);
		$cmd->CreaParametro("@velmul",$velmul,1);
		$cmd->CreaParametro("@cache",$cache,1);
		$cmd->CreaParametro("@idperfilhard",$idperfilhard,1);
		$cmd->CreaParametro("@idservidordhcp",$idservidordhcp,1);
		$cmd->CreaParametro("@idservidorrembo",$idservidorrembo,1);

		for($i=0;$i<$numorde;$i++){
			if($swipmul)
				$cmd->ParamSetValor("@ipmul",$litnwipmul.$nwipmul);
			if($swnom && $swipmul)
				$cmd->ParamSetValor("@modomul",$modomul.$nwipmul);
			$cmd->texto="INSERT INTO ordenadores(modomul,ipmul,pormul,velmul,cache,idperfilhard,idservidordhcp,idservidorrembo,idaula,grupoid,idconfiguracion) VALUES (@modomul,@ipmul,@pormul,@velmul,@cache,@idperfilhard,@idservidordhcp,@idservidorrembo,@idaula,@grupoid,0)";
			if($swipmul) $nwipmul++;
			$resul=$cmd->Ejecutar();
			if (!$resul) return(false);
		}
	}
	else{
		$strsql="UPDATE ordenadores SET ";
		if (!empty($modomul))	$strsql.=" modomul='".$modomul."',";
		if (!empty($ipmul))	$strsql.=" ipmul='".$ipmul."',";
		if (!empty($pormul))	$strsql.=" pormul='".$pormul."',";
		if (!empty($velmul))	$strsql.=" velmul='".$velmul."',";
		$strsql.=" cache='".$cache."',";
		if ($idperfilhard>0)	$strsql.=" idperfilhard=".$idperfilhard.",";
		if ($idservidordhcp>0)	$strsql.=" idservidordhcp=".$idservidordhcp.",";
		if ($idservidorrembo>0)	$strsql.=" idservidorrembo=".$idservidorrembo.",";
		$strsql=substr($strsql,0,strlen($strsql)-1); // Quita la coma final
		$strsql.=" WHERE idaula=".$idaula;
		$cmd->texto=$strsql;
		$resul=$cmd->Ejecutar();
	}
	return($resul);
}
?>
