<?
$wer="http://10.1.15.4/WebConsole/pagerror.php"; // P�ina de redireccionamiento de errores
$wac="http://10.1.15.4/WebConsole/acceso.php"; // P�ina de login de la aplicaci�
//========================================================================================================
// Variables de sessi� de configuraci� de servidor y base de datos( Modificar aqu�para cambio global) 
$cnx="localhost;usuog;passusuog;ogBDAdmin;mysql"; // Cadena de conexi� a la base de datos
$ips="10.1.15.4"; // IP del servidor hidra
$prt="2008"; // Puerto de comunicaci� con el servidor
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
	Header("Location: ".$wac."?herror=4"); // Error de conexi� con servidor B.D.

$_SESSION["widcentro"]=$idc; 
$_SESSION["wnombrecentro"]=$nmc; 
$_SESSION["wusuario"]=$usu; 
$_SESSION["widtipousuario"]=$tsu; 
$_SESSION["widioma"]=$idi;
$_SESSION["wcadenaconexion"]=$cnx;
$_SESSION["wservidorhidra"]=$ips;
$_SESSION["whidraport"]=$prt;
$_SESSION["wpagerror"]=$wer;
$_SESSION["wurlacceso"]=$wac;

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
