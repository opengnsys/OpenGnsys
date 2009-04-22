<?
// *************************************************************************************************************************************************
// Aplicaci� WEB: Hidra
// Copyright 2003-2005  Jos�Manuel Alonso. Todos los derechos reservados.
// Fecha Creaci�: A� 2003-2004
// Fecha �tima modificaci�: Marzo-2005
// Nombre del fichero: CrearPerfilSoftware.php
// Descripci� : 
//		Implementaci� del comando "CrearPerfilSoftware"
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/TomaDato.php");
include_once("../idiomas/php/".$idioma."/comandos/crearperfilsoftware_".$idioma.".php");
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
$idperfilsoftware=0; 
$idordenador=$idambito; 
$nombreordenador="";
$ip="";
$mac="";
$idperfilhard=0;
$idservidordhcp=0;
$idservidorrembo=0;

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexi� con servidor B.D.
$resul=toma_propiedades($cmd,$idordenador);
if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperaci� de datos.
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administraci� web de aulas</TITLE>
<HEAD>
<LINK rel="stylesheet" type="text/css" href="../hidra.css">
<SCRIPT language="javascript" src="./jscripts/CrearPerfilSoftware.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/crearperfilsoftware_'.$idioma.'.js"></SCRIPT>'?>
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
		- cmd: Una comando ya operativo (con conexi� abierta)  
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
function HTMLSELECT_perfiles($cmd,$idcentro,$tipopart,$particion){
	$SelectHtml="";
	$rs=new Recordset; 
	$cmd->texto="SELECT  perfilessoft.idperfilsoft,perfilessoft.descripcion,tiposos.nemonico 
				FROM  tiposos 
				INNER JOIN softwares ON tiposos.idtiposo = softwares.idtiposo 
				INNER JOIN perfilessoft_softwares ON softwares.idsoftware = perfilessoft_softwares.idsoftware 
				INNER JOIN perfilessoft ON  perfilessoft.idperfilsoft = perfilessoft_softwares.idperfilsoft 
				
				WHERE perfilessoft.idcentro=".$idcentro;
	// Cuesti� partici� oculta
	 $swo=substr ($tipopart,0,1);
	if($swo=="H") 
		 $tipopart=substr ($tipopart,1,strlen($tipopart)-1);
	$cmd->texto.=" AND (tiposos.tipopar = '".$tipopart."' OR tiposos.tipopar ='H".$tipopart."' )";
	$cmd->texto.=" AND tiposos.tipopar = '".$tipopart."'";
	$rs->Comando=&$cmd; 

	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$SelectHtml.= '<SELECT class="formulariodatos" id="desple_'.$particion.'" style="WIDTH: 300">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';
	$rs->Primero(); 
	while (!$rs->EOF){
		$SelectHtml.='<OPTION value="'.$rs->campos["idperfilsoft"].'">';
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
	$rsp=new Recordset; 
	$cmd->texto="SELECT configuraciones.configuracion FROM configuraciones INNER JOIN ordenadores ON configuraciones.idconfiguracion=ordenadores.idconfiguracion WHERE ordenadores.idordenador='".$idordenador."'";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	$configuracion= $rs->campos["configuracion"];
	$auxsplit=split("\t",$configuracion);
	for($j=0;$j<sizeof($auxsplit)-1;$j++){
		$ValorParametros=extrae_parametros($auxsplit[$j],chr(10),'=');
		$particion=$ValorParametros["numpart"]; // Toma la partici�
		$tiposo=$ValorParametros["tiposo"]; // Toma nombre del sistema operativo
		$tipopart=trim($ValorParametros["tipopart"]); // Toma tipo de partici� del sistema operativo
		$nombreso=$ValorParametros["nombreso"]; // Toma nombre del sistema operativo
		if(!empty($tiposo)){
			$tablaHtml.='<TR>'.chr(13);
			$tablaHtml.='<TD ><input type=checkbox name=particion_'.$particion.' value='.$particion.'></TD>'.chr(13);
			$tablaHtml.='<TD align=center>&nbsp;'.$particion.'&nbsp;</TD>'.chr(13);
			$tablaHtml.='<TD>&nbsp;'.$nombreso.'&nbsp;</TD>'.chr(13);
			$tiposo=$ValorParametros["tiposo"];
			$tablaHtml.='<TD>'.HTMLSELECT_perfiles($cmd,$idcentro,$tipopart,$particion).'</TD>';
			$tablaHtml.='</TR>'.chr(13);
		}
	}
	$rs->Cerrar();
	return($tablaHtml);
}
?>