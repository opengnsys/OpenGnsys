<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: CrearSoftIncremental.php
// Descripción : 
//		Implementación del comando "CrearSoftIncremental"
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/TomaDato.php");
include_once("../idiomas/php/".$idioma."/comandos/CrearSoftIncremental_".$idioma.".php");
//________________________________________________________________________________________________________
$identificador=0;
$nombrefuncion="";
$ejecutor="";
$tipotrama=""; 
$ambito=0; 
$idambito=0;
$cadenaip="";

$fp = fopen($fileparam,"r"); 
$parametros= fread ($fp, filesize ($fileparam));
fclose($fp);

$ValorParametros=extrae_parametros($parametros,chr(13),'=');
$identificador=$ValorParametros["identificador"]; 
$nombrefuncion=$ValorParametros["nombrefuncion"]; 
$ejecutor=$ValorParametros["ejecutor"]; 
$tipotrama=$ValorParametros["tipotrama"]; 
$ambito=$ValorParametros["ambito"]; 
$idambito=$ValorParametros["idambito"]; 
$cadenaip=$ValorParametros["cadenaip"]; 
//________________________________________________________________________________________________________
$idsoftincrementalware=0; 
$idordenador=$idambito; 
$nombreordenador="";
$ip="";
$mac="";
$idperfilhard=0;
$idservidordhcp=0;
$idservidorrembo=0;

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
$resul=toma_propiedades($cmd,$idordenador);
if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="./jscripts/CrearSoftIncremental.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/CrearSoftIncremental_'.$idioma.'.js"></SCRIPT>'?>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<INPUT type=hidden name=identificador value=<? echo $identificador ?>>
	<INPUT type=hidden name=nombrefuncion value=<? echo $nombrefuncion ?>>
	<INPUT type=hidden name=ejecutor value=<? echo $ejecutor ?>>
	<INPUT type=hidden name=tipotrama value=<? echo $tipotrama ?>>
	<INPUT type=hidden name=ambito value=<? echo $ambito ?>>
	<INPUT type=hidden name=idambito value=<? echo $idambito ?>>
	<INPUT type=hidden name=cadenaip value=<? echo $cadenaip ?>>
	<INPUT type=hidden name=idperfilhard value=<? echo $idperfilhard ?>>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<P align=center class=cabeceras><? echo $TbMsg[0] ?><P>
	<P align=center>
	<SPAN align=center class=subcabeceras><? echo $TbMsg[1] ?></SPAN>
	</BR>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;<? echo $TbMsg[2] ?>&nbsp;</TD>
			<? echo '<TD>'.$nombreordenador.'</TD>';?>
			<TD colspan=2 valign=top align=left rowspan=3><IMG border=2 style="border-color:#63676b" src="../images/fotoordenador.gif"></TD>
		</TR>	
		<TR>
			<TH align=center>&nbsp;<? echo $TbMsg[3] ?>&nbsp;</TD>
			<? echo '<TD>'.$ip.'</TD>';?>
		</TR>
		<TR>
			<TH align=center>&nbsp;<? echo $TbMsg[4] ?>&nbsp;</TD>
			<? echo '<TD>'.$mac.'</TD>';?>
		</TR>	
		<TR>
			<TH align=center>&nbsp;<? echo $TbMsg[5] ?>&nbsp;</TD>
			<? echo '<TD colspan=3>'.TomaDato($cmd,$idcentro,'perfileshard',$idperfilhard,'idperfilhard','descripcion').'</TD>';	?>
		</TR>
	</TABLE>
	</P>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<P align=center>
	<SPAN align=center class=subcabeceras><? echo $TbMsg[6] ?></SPAN>
	</BR>
	<TABLE  id="tabla_conf" align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;&nbsp;</TH>
			<TH align=center>&nbsp;<? echo $TbMsg[8] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<? echo $TbMsg[9] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<? echo $TbMsg[10] ?>&nbsp;</TD></TR>
			<?
				echo tabla_configuraciones($cmd,$idordenador);
			?>
	</TABLE>
</FORM>
<?
//________________________________________________________________________________________________________
include_once("../includes/opcionesacciones.php");
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotones.php");
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
/**************************************************************************************************************************************************
	Recupera los datos de un ordenador
		Parametros: 
		- cmd: Una comando ya operativo (con conexión abierta)  
		- ido: El identificador del ordenador
________________________________________________________________________________________________________*/
function toma_propiedades($cmd,$ido){
	global $nombreordenador;
	global $ip;
	global $mac;
	global $idperfilhard;
	global $idservidordhcp;
	global $idservidorrembo;
	$rs=new Recordset; 
	$cmd->texto="SELECT nombreordenador,ip,mac,idperfilhard FROM ordenadores WHERE idordenador='".$ido."'";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombreordenador=$rs->campos["nombreordenador"];
		$ip=$rs->campos["ip"];
		$mac=$rs->campos["mac"];
		$idperfilhard=$rs->campos["idperfilhard"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de los perfiles softwares
________________________________________________________________________________________________________*/
function HTMLSELECT_incrementales($cmd,$idcentro,$idperfilsoft,$particion){
	$SelectHtml="";
	$rs=new Recordset; 

	$cmd->texto="SELECT     softincrementales.idsoftincremental, softincrementales.descripcion, tiposoftwares.idtiposoftware FROM         softincrementales INNER JOIN softwares INNER JOIN softincremental_softwares ON softwares.idsoftware = softincremental_softwares.idsoftware ON  softincrementales.idsoftincremental = softincremental_softwares.idsoftincremental INNER JOIN perfilessoft_softwares ON softwares.idsoftware = perfilessoft_softwares.idsoftware INNER JOIN perfilessoft ON perfilessoft_softwares.idperfilsoft = perfilessoft.idperfilsoft INNER JOIN tiposoftwares ON softwares.idtiposoftware = tiposoftwares.idtiposoftware";

	$cmd->texto.=" WHERE     (softincrementales.idcentro = ".$idcentro.") AND (perfilessoft.idperfilsoft = ".$idperfilsoft.") AND (tiposoftwares.idtiposoftware = 1)";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$SelectHtml.= '<SELECT class="formulariodatos" id="desple_'.$particion.'" style="WIDTH: 300">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';
	$rs->Primero(); 
	while (!$rs->EOF){
		$SelectHtml.='<OPTION value="'.$idperfilsoft.'_'.$rs->campos["idsoftincremental"].'">';
		$SelectHtml.= $rs->campos["descripcion"].'</OPTION>';
		$rs->Siguiente();
	}
	$SelectHtml.= '</SELECT>';
	$rs->Cerrar();
	return($SelectHtml);
}
/*________________________________________________________________________________________________________
	Crea la tabla de configuraciones y perfiles a crear
________________________________________________________________________________________________________*/
function tabla_configuraciones($cmd,$idordenador){
	global $idcentro;
	$tablaHtml="";
	$rs=new Recordset; 
	$cmd->texto="SELECT  ordenadores.idordenador,perfilessoft.idperfilsoft, perfilessoft.descripcion, ordenadores.ip, ordenador_imagen.particion FROM ordenadores INNER JOIN ordenador_imagen ON ordenadores.idordenador = ordenador_imagen.idordenador INNER JOIN imagenes ON ordenador_imagen.idimagen = imagenes.idimagen INNER JOIN  perfilessoft ON imagenes.idperfilsoft = perfilessoft.idperfilsoft WHERE ordenadores.idordenador=".$idordenador." ORDER BY ordenador_imagen.particion ";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	while (!$rs->EOF){
		$tablaHtml.='<TR>'.chr(13);
		$tablaHtml.='<TD ><input type=checkbox name=particion_'.$rs->campos["particion"].' value='.$rs->campos["particion"].'></TD>'.chr(13);
		$tablaHtml.='<TD align=center>&nbsp;'.$rs->campos["particion"].'&nbsp;</TD>'.chr(13);
		$tablaHtml.='<TD>&nbsp;'.$rs->campos["descripcion"].'&nbsp;</TD>'.chr(13);
		$tablaHtml.='<TD>'.HTMLSELECT_incrementales($cmd,$idcentro,$rs->campos["idperfilsoft"],$rs->campos["particion"]).'</TD>';
		$tablaHtml.='</TR>'.chr(13);
		$rs->Siguiente();
	}
	$rs->Cerrar();
	return($tablaHtml);
}
?>











