<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: configuracionordenador.php
// Descripción : 
//		Muestra la configuraci� de las particiones de un ordenador
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/TomaDato.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/comunes.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/switchparticiones.php");
include_once("../idiomas/php/".$idioma."/configuracionordenador_".$idioma.".php");
//________________________________________________________________________________________________________
$idordenador=0;
if (isset($_GET["idordenador"])) $idordenador=$_GET["idordenador"]; 
//________________________________________________________________________________________________________
$nombreordenador="";
$ip="";
$mac="";
$idperfilhard=0;
$idservidordhcp=0;
$idservidorrembo=0;

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.
$resul=TomaPropiedades($cmd,$idordenador);
if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperaci� de datos.
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<P align=center class=cabeceras><?echo $TbMsg[0]?></P>
	<P align=center><SPAN align=center class=subcabeceras><? echo $TbMsg[1]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[2]?>&nbsp;</TD>
			<? echo '<TD>'.$nombreordenador.'</TD>';?>
			<TD colspan=2 valign=top align=left rowspan=3><IMG border=2 style="border-color:#63676b" src="../images/fotoordenador.gif"></TD>
			</TR>	
		<TR>
				<TH align=center>&nbsp;<?echo $TbMsg[3]?>&nbsp;</TD>
				<?echo '<TD>'.$ip.'</TD>';?>
			</TR>
		<TR>
				<TH align=center>&nbsp;<?echo $TbMsg[4]?>&nbsp;</TD>
				<? echo '<TD>'.$mac.'</TD>';?>
			</TR>	
		<TR>
				<TH align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TD>
				<?echo '<TD colspan=3>'.TomaDato($cmd,$idcentro,'perfileshard',$idperfilhard,'idperfilhard','descripcion').'</TD>';?>
		</TR>
	</TABLE>
</FORM>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	<P align=center><SPAN align=center class=subcabeceras><? echo $TbMsg[9]?></SPAN></P>
	<? echo tabla_configuraciones($cmd,$idcentro,$idordenador);	?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
function TomaPropiedades($cmd,$ido){
	global $idordenador; 
	global $nombreordenador;
	global $ip;
	global $mac;
	global $idperfilhard;
	global $idservidordhcp;
	global $idservidorrembo;
	$rs=new Recordset; 
	$cmd->texto="SELECT nombreordenador,ip,mac,idperfilhard FROM ordenadores WHERE idordenador=".$ido;
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
//________________________________________________________________________________________________________
function tabla_configuraciones($cmd,$idcentro,$idordenador){
	global $TbMsg;
	$tablaHtml="";
	$rs=new Recordset; 
	$rsp=new Recordset; 
	$cmd->texto="SELECT configuraciones.configuracion FROM configuraciones INNER JOIN ordenadores ON configuraciones.idconfiguracion=ordenadores.idconfiguracion WHERE ordenadores.idordenador='".$idordenador."'";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	$configuracion= $rs->campos["configuracion"];
	$auxsplit=split("\t",$configuracion);
	$tablaHtml.= '<TABLE  class=tabla_listados_sin id="tabla_conf" align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>';
	$tablaHtml.= '<TR>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[6].'&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[7].'&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[8].'&nbsp;</TH>';
	$tablaHtml.= '</TR>';
		for($j=0;$j<sizeof($auxsplit)-1;$j++){
			$ValorParametros=extrae_parametros($auxsplit[$j],chr(10),'=');
			$particion=$ValorParametros["numpart"]; // Toma la partici�
			$nombreso=$ValorParametros["nombreso"]; // Toma nombre del sistema operativo
			$tiposo=$ValorParametros["tiposo"];
			$tipopart=$ValorParametros["tipopart"];
			$tamapart=$ValorParametros["tamapart"];
			$tablaHtml.='<TR>'.chr(13);
			$tablaHtml.='<TD align=center>&nbsp;'.$particion.'&nbsp;</TD>'.chr(13);
			$tablaHtml.='<TD>&nbsp;'. nombreSO($tipopart,$tiposo,$nombreso).'&nbsp;</TD>'.chr(13);
			$tablaHtml.='<TD align=rigth>&nbsp;'. formatomiles( $tamapart).'&nbsp;</TD>'.chr(13);
			$tablaHtml.='</TR>'.chr(13);
	}
	$tablaHtml.='</TABLE>';
	return($tablaHtml);
}
//________________________________________________________________________________________________________
function formatomiles($cadena){
	$len=strlen($cadena);
	$cadenafinal="";
	$m=1;
	for($i=$len-1;$i>=0;$i--){
		$cadenafinal=substr($cadena,$i,1).$cadenafinal;
		if($m%3==0 && $i>0){
				$cadenafinal=".".$cadenafinal;
				$m=0;
		}
		$m++;
	}
	return($cadenafinal);
}
?>