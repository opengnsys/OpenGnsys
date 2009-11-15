<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: InventarioSoftware.php
// Descripción : 
//		Implementación del comando "Inventario Software"
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../idiomas/php/".$idioma."/comandos/inventariosoftware_".$idioma.".php");
//________________________________________________________________________________________________________
$fp = fopen($fileparam,"r"); 
$parametros= fread ($fp, filesize ($fileparam));
fclose($fp);

$ValorParametros=extrae_parametros($parametros,chr(13),'=');
$idambito=$ValorParametros["idambito"]; 
$ambito=$ValorParametros["ambito"]; 
$nombreambito=$ValorParametros["nombreambito"]; 

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="./jscripts/InventarioSoftware.js"></SCRIPT>
	<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<?
switch($ambito){
		case $AMBITO_CENTROS :
			$urlimg='../images/iconos/centros.gif';
			$textambito=$TbMsg[0];
			break;
		case $AMBITO_GRUPOSAULAS :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[1];
			break;
		case $AMBITO_AULAS :
			$urlimg='../images/iconos/aula.gif';
			$textambito=$TbMsg[2];
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[3];
			break;
		case $AMBITO_ORDENADORES :
			$urlimg='../images/iconos/ordenador.gif';
			$textambito=$TbMsg[4];
			break;
	}
	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>';
	echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras><U>'.$TbMsg[6].': '.$textambito.','.$nombreambito.'</U></span>&nbsp;&nbsp;</span></p>';
?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<P align=center>
	<SPAN align=center class=subcabeceras><? echo $TbMsg[7] ?></SPAN>
	</BR>
	<TABLE  id="tabla_conf" align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;&nbsp;</TH>
			<TH align=center>&nbsp;<? echo $TbMsg[8] ?>&nbsp;</TH>
			<TH align=center>&nbsp;<? echo $TbMsg[9] ?>&nbsp;</TH>
		</TR>
			<?
				echo tabla_configuraciones($cmd,$idambito);
			?>
	</TABLE>

<BR>
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
/*________________________________________________________________________________________________________
	Particiones
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
			$tablaHtml.='</TR>'.chr(13);
		}
	}
	$rs->Cerrar();
	return($tablaHtml);
}
?>
