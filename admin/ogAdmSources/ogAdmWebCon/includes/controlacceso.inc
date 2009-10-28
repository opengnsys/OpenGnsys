<?
$wer="http://10.1.15.3/ogAdmWebCon/pagerror.php"; // Página de redireccionamiento de errores
$wac="http://10.1.15.3/ogAdmWebCon/acceso.php"; // Página de login de la aplicación
//========================================================================================================
// Variables de sessión de configuración de servidor y base de datos( Modificar aquí para cambio global) 
//$cnx="10.1.15.3;usuhidra;passusuhidra;ogBDAdmin;sqlserver"; // Cadena de conexión a la base de datos
$cnx="10.1.15.3;usuhidra;passusuhidra;ogBDAdmin;sqlserver"; // Cadena de conexión a la base de datos
$ips="10.1.15.3"; // IP del servidor hidra
$prt="2008"; // Puerto de comunicación con el servidor
//========================================================================================================
$cmd=CreaComando($cnx); // Crea objeto comando
$resul=false;
$idc=0;
$nmc="";
$idi="";
if ($cmd){
	$resul=toma_datos($cmd,&$idc,&$nmc,&$idi,$usu,&$tsu,$pss);
}
if(!$resul)
	Header("Location: ".$wac."?herror=4"); // Error de conexión con servidor B.D.

session_start(); // Activa variables de sesión
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
//	Devuelve una objeto comando totalmente operativo (con la conexión abierta)
//	Parametros: 
//		- cadenaconexion: Una cadena con los datos necesarios para la conexión: nombre del servidor
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
//	Busca datos del usuario que intenta acceder a la aplicación 
//		Parametros: 
//		- cmd:Una comando ya operativo (con conexión abierta)  
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
