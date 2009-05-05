<?
// *************************************************************************************************************************************************
// Aplicaci� WEB: Hidra
// Copyright 2003-2005 Jos�Manuel Alonso. Todos los derechos reservados.
// Fecha Creaci�: Diciembre-2003
// Fecha �tima modificaci�: Febrero-2005
// Nombre del fichero: controlacceso.php
// Descripci� :Este fichero implementa el control de acceso a la aplicaci�
// *************************************************************************************************************************************************
include_once("./clases/AdoPhp.php");

//========================================================================================================
// Variables de sessi� de configuraci� de servidor y base de datos( Modificar aqu�para cambio global) 
$cnx="localhost;usuhidra;passusuhidra;bdhidra;sqlserver"; // Cadena de conexi� a la base de datos
$ips="10.1.15.3"; // IP del servidor hidra
$prt="2008"; // Puerto de comunicaci� con el servidor
$wer="http://10.1.15.3/hidraweb/pagerror.php"; // P�ina de redireccionamiento de errores
$wac="http://10.1.15.3/hidraweb/acceso.php"; // P�ina de login de la aplicaci�
//========================================================================================================

$usu="";
$pss="";
if (isset($_POST["usu"])) $usu=$_POST["usu"]; 
if (isset($_POST["pss"])) $pss=$_POST["pss"]; 

$cmd=CreaComando($cnx); // Crea objeto comando
$resul=false;
$idc=0;
$nmc="";
$idi="";
if ($cmd){
	$resul=toma_datos($cmd,&$idc,&$nmc,&$idi,$usu,&$tsu,$pss);
	$cmd->Conexion->Cerrar();
}

if(!$resul)
	Header("Location: ".$wac."?herror=4"); // Error de conexi� con servidor B.D.

session_start(); // Activa variables de sesi�
$_SESSION["idcentro"]=$idc; 
$_SESSION["nombrecentro"]=$nmc; 
$_SESSION["usuario"]=$usu; 
$_SESSION["idtipousuario"]=$tsu; 
$_SESSION["idioma"]=$idi;
$_SESSION["cadenaconexion"]=$cnx;
$_SESSION["servidorhidra"]=$ips;
$_SESSION["hidraport"]=$prt;
$_SESSION["pagerror"]=$wer;
$_SESSION["urlacceso"]=$wac;

// *************************************************************************************************************************************************
//	Devuelve una objeto comando totalmente operativo (con la conexi� abierta)
//	Parametros: 
//		- cadenaconexion: Una cadena con los datos necesarios para la conexi�: nombre del servidor
//		usuario,password,base de datos,etc separados por coma
//________________________________________________________________________________________________________
function CreaComando($cadenaconexion){
	$strcn=split(";",$cadenaconexion);
	$cn=new Conexion; 
	$cmd=new Comando;	
	$cn->CadenaConexion($strcn[0],$strcn[1],$strcn[2],$strcn[3],$strcn[4]);
	if (!$cn->Abrir()) return (false); 
	$cmd->Conexion=&$cn; 
	return($cmd);
}
//________________________________________________________________________________________________________
//	Busca datos del usuario que intenta acceder a la aplicaci� 
//		Parametros: 
//		- cmd:Una comando ya operativo (con conexi� abierta)  
//		- usuario: Nombre del usuario  
//		- pasguor: Password del uuario  
//
//	Devuelve el identificador del centro, el nombre y el idioma utilizado por el usuario 
//________________________________________________________________________________________________________
function toma_datos($cmd,$idcentro,$nombrecentro,$idioma,$usuario,$idtipousuario,$pasguor){
	$rs=new Recordset; 

	$cmd->texto="SELECT usuarios.idtipousuario,usuarios.idambito,centros.nombrecentro,idiomas.nemonico AS idioma FROM usuarios";
	$cmd->texto.=" LEFT OUTER JOIN centros ON usuarios.idambito=centros.idcentro";
	$cmd->texto.=" INNER JOIN idiomas ON usuarios.ididioma=idiomas.ididioma";
	$cmd->texto.=" WHERE idtipousuario<>3 AND usuarios.usuario='".$usuario."' AND usuarios.pasguor='".$pasguor."'";

	$rs->Comando=&$cmd; 
	$resul=false;
	if (!$rs->Abrir()) return($resul); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$idcentro=$rs->campos["idambito"];
		$nombrecentro=$rs->campos["nombrecentro"];
		$idtipousuario=$rs->campos["idtipousuario"];
		$idioma=$rs->campos["idioma"];
		return(true);
	}
	return($resul);
}
?>
<HTML>
	<TITLE> Administraci� web de aulas</TITLE>
	<HEAD>
		<LINK rel="stylesheet" type="text/css" href="hidra.css">
	</HEAD>
	<BODY>
		<DIV id="mensaje" style="Position:absolute;TOP:250;LEFT:330; visibility:visible">
		<SPAN  align=center class=subcabeceras>Acceso permitido. Espere por favor ...</SPAN></P>
		<SCRIPT LANGUAGE="JAVASCRIPT">
			var vez=0;
			setTimeout("acceso();",300);
			function acceso(){
				o=document.getElementById("mensaje");
				var s=o.style.visibility;
				if(s=="hidden")
					o.style.visibility="visible";
				else
					o.style.visibility="hidden";
				if(vez>5){
					var w=window.top;
					w.location="frames.php";
				}
				vez++;
				setTimeout("acceso();",300);
			}
	</SCRIPT>
 </BODY>
</HTML>
