<?
// *************************************************************************************************************************************************
// Aplicaci� WEB: Hidra
// Copyright 2003-2005  Jos�Manuel Alonso. Todos los derechos reservados.
// Fecha Creaci�: A� 2003-2004
// Fecha �tima modificaci�: Marzo-2005
// Nombre del fichero: gestor_EjecutarScripts.php
// Descripci� : 
//		Gestor del comando "EjecutarScripts"
// *************************************************************************************************************************************************
include_once("../../includes/ctrlacc.php");
include_once("../../clases/AdoPhp.php");
include_once("../../clases/SockHidra.php");
include_once("../../includes/constantes.php");
include_once("../../includes/comunes.php");
include_once("../../includes/cuestionacciones.php");
include_once("../../includes/CreaComando.php");
include_once("../../idiomas/php/".$idioma."/comandos/gestor_ejecutarscripts_".$idioma.".php");
//________________________________________________________________________________________________________
$identificador=0;
$nombrefuncion="";
$ejecutor="";
$tipotrama=""; 
$ambito=0; 
$idambito=0;
$cadenaip="";
$titulo="";
$descripcion="";
$pseudocodigo="";
$filescript="";

if (isset($_POST["titulo"]))	$titulo=$_POST["titulo"]; 
if (isset($_POST["descripcion"]))	$descripcion=$_POST["descripcion"]; 
if (isset($_POST["pseudocodigo"]))	$pseudocodigo=$_POST["pseudocodigo"]; 

if (isset($_POST["identificador"]))	$identificador=$_POST["identificador"]; 
if (isset($_POST["nombrefuncion"]))	$nombrefuncion=$_POST["nombrefuncion"]; 
if (isset($_POST["ejecutor"]))	$ejecutor=$_POST["ejecutor"]; 

if (isset($_POST["tipotrama"]))	$tipotrama=$_POST["tipotrama"]; 
if (isset($_POST["ambito"]))	$ambito=$_POST["ambito"]; 
if (isset($_POST["idambito"]))	$idambito=$_POST["idambito"]; 
if (isset($_POST["cadenaip"]))	$cadenaip=$_POST["cadenaip"]; 

/*
	// Se env� fichero de script
$ficheroPOST = $HTTP_POST_FILES['userfile']['tmp_name']; 
$nombreOriginal_archivo = $HTTP_POST_FILES['userfile']['name']; 
$tamano_archivo = $HTTP_POST_FILES['userfile']['size']; 
*/
$URLPATHFILESCRIPT="./filescripts";
$FISPATHFILESCRIPT=realpath($URLPATHFILESCRIPT);
$NOMBREFILESCRIPT="cmdscript.rbc";
$ficheroLOCAL=$FISPATHFILESCRIPT."/".$NOMBREFILESCRIPT;

$sw_ejya="";
$sw_seguimiento="";
$sw_mktarea="";
$nwidtarea="";
$nwdescritarea="";
$sw_mkprocedimiento="";
$nwidprocedimiento="";
$nwdescriprocedimiento="";

if (isset($_POST["sw_ejya"]))	$sw_ejya=$_POST["sw_ejya"]; 
if (isset($_POST["sw_seguimiento"]))	$sw_seguimiento=$_POST["sw_seguimiento"]; 
if (isset($_POST["sw_mktarea"]))	$sw_mktarea=$_POST["sw_mktarea"]; 
if (isset($_POST["nwidtarea"]))	$nwidtarea=$_POST["nwidtarea"]; 
if (isset($_POST["nwdescritarea"]))	$nwdescritarea=$_POST["nwdescritarea"]; 
if (isset($_POST["sw_mkprocedimiento"]))	$sw_mkprocedimiento=$_POST["sw_mkprocedimiento"]; 
if (isset($_POST["nwidprocedimiento"]))	$nwidprocedimiento=$_POST["nwidprocedimiento"]; 
if (isset($_POST["nwdescriprocedimiento"]))	$nwdescriprocedimiento=$_POST["nwdescriprocedimiento"]; 

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
$resul=0;
if ($cmd){
	$resul=Gestiona($cmd);
}
$cmd->Conexion->Cerrar();
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administraci� web de aulas</TITLE>
<HEAD>
<LINK rel="stylesheet" type="text/css" href="../hidra.css">
</HEAD>
<BODY>
<?
echo '<SCRIPT language="javascript">'.chr(13);
echo '  var msg=new Array()'.chr(13);
echo '  msg[1]='.$TbMsg[1].chr(13);
echo '  msg[2]='.$TbMsg[2].chr(13);
echo '  msg[3]='.$TbMsg[3].chr(13);
echo '  msg[4]='.$TbMsg[4].chr(13);
echo '  msg[5]='.$TbMsg[5].chr(13);
echo 'alert( msg[' .$resul.'])';
echo '</SCRIPT>';
?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
function Gestiona($cmd){
	global $ACCION_SINERRORES; // Activa y con algn error
	global $ACCION_INICIADA;
	global $MAXSIZEFILERBC;
	global $idcentro;
	global $cadenaip;
	global $identificador;
	global $nombrefuncion;
	global $ejecutor;
	global $tipotrama; 
	global $ambito; 
	global $idambito;
	global $titulo;
	global $descripcion;
	global $pseudocodigo;
	global $filescript;
	global $EJECUCION_COMANDO;
	global $PROCESOS;
	global $FISPATHFILESCRIPT;
	global $servidorhidra;
	global $hidraport;
	global $nombre_archivo;
	global $nombreOriginal_archivo;
	global $tamano_archivo;
	global $ficheroPOST;
	global $ficheroLOCAL;

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
	$parametros.="tis=".$titulo.chr(13);
	$parametros.="dcr=".$descripcion.chr(13);

	// Se env� fichero de script 
	if(!empty($ficheroPOST)){
		$posrbc=strpos($nombreOriginal_archivo, "rbc");
		if ($posrbc>0 && $tamano_archivo < $MAXSIZEFILERBC) { 
			if (salvafichero_POST($ficheroPOST,$ficheroLOCAL)){
				$fp = fopen ($ficheroLOCAL, "r");
				$pseudocodigo = fread ($fp, filesize ($ficheroLOCAL));
				fclose ($fp);
				if(empty($pseudocodigo)) // No hay c�igo que ejecutar
					return(4); // El fichero no contiene c�igo
			}
			else
				return(5); // No se puede salvar el fichero de script enviado por POST
		}
		else{
				return(3); // El fichero no tiene la extension .rbc
		}
	}
	$fp = fopen($ficheroLOCAL,"w"); 
	fwrite($fp, $pseudocodigo,strlen($pseudocodigo)); 
	fclose($fp); 

	$parametros.="scp=".$pseudocodigo.chr(13);
	$parametros.="iph=".$cadenaip.chr(13);
	$cmd->ParamSetValor("@parametros",$parametros);
	if(!CuestionAcciones($cmd,$shidra,$parametros)) return(2);
	return(1);
}
//________________________________________________________________________________________________________
//
//	Salva un fichero enviado por POST
//________________________________________________________________________________________________________
function salvafichero_POST($ficheroPost,$ficheroLocal){
	if (file_exists($ficheroLocal)) // Borra el fichero si existe
        unlink($ficheroLocal);
	return(move_uploaded_file($ficheroPost,$ficheroLocal)); // salva el fichero
}
?>