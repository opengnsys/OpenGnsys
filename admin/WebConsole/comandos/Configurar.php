<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: Configurar.php
// Descripción : 
//		Implementación del comando "Configurar"
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../idiomas/php/".$idioma."/comandos/configurar_".$idioma.".php");
//________________________________________________________________________________________________________
$identificador=0;
$nombrefuncion="";
$ejecutor="";
$tipotrama=""; 
$ambito=0; 
$idambito=0;
$nombreambito="";
$cadenaip="";
$tbconfigur="";

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
$nombreambito=$ValorParametros["nombreambito"]; 
$cadenaip=$ValorParametros["cadenaip"]; 

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.
//___________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="./jscripts/Configurar.js"></SCRIPT>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/configurar_'.$idioma.'.js"></SCRIPT>'?>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatosocultos"> 
	<INPUT type=hidden name=identificador value=<? echo $identificador ?>>
	<INPUT type=hidden name=nombrefuncion value=<? echo $nombrefuncion ?>>
	<INPUT type=hidden name=ejecutor value=<? echo $ejecutor ?>>
	<INPUT type=hidden name=tipotrama value=<? echo $tipotrama ?>>
	<INPUT type=hidden name=ambito value=<? echo $ambito ?>>
	<INPUT type=hidden name=idambito value=<? echo $idambito ?>>
	<INPUT type=hidden name=cadenaip value=<? echo $cadenaip ?>>
</FORM>
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
//________________________________________________________________________________________________________
?>	
<BR>
	<FORM  name="fdatos"> 
			<?
				echo tabla_configuraciones($cmd,$idcentro,$idambito,$ambito);
				echo '<TABLE border=0 style="visibility: hidden" id=patron_contenidoparticion>'.Patrontabla_Particion().'</TABLE>';
				echo '<INPUT type=hidden id=tbconfigur value="'.$tbconfigur.'">';
			?>
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
// ***********************************************************************************************************
function tabla_configuraciones($cmd,$idcentro,$idambito,$ambito){
	global $cadenaip;
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;
	
	$tablaHtml="";
	$rs=new Recordset; 
	$numorde=0;

	switch($ambito){
		case $AMBITO_AULAS :
			$cmd->texto="SELECT COUNT(*) AS numorde FROM ordenadores WHERE idaula=".$idambito;
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$cmd->texto="SELECT COUNT(*) AS numorde FROM ordenadores WHERE grupoid=".$idambito;
			break;
		case $AMBITO_ORDENADORES :
			$cmd->texto="SELECT COUNT(*) AS numorde FROM ordenadores WHERE idordenador=".$idambito;
			break;
	}
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	if(!$rs->EOF)
		$numorde=$rs->campos["numorde"];
	$idconfiguracion="";

	switch($ambito){
		case $AMBITO_AULAS :
			$cmd->texto="SELECT COUNT(*) AS cuenta,configuraciones.descripcion,configuraciones.idconfiguracion FROM aulas";
			$cmd->texto.=" INNER JOIN ordenadores ON aulas.idaula = ordenadores.idaula";
			$cmd->texto.=" INNER JOIN configuraciones ON ordenadores.idconfiguracion = configuraciones.idconfiguracion";
			$cmd->texto.=" WHERE aulas.idaula = ".$idambito;
			$cmd->texto.=" GROUP BY configuraciones.descripcion, configuraciones.idconfiguracion";
			$cmd->texto.=" HAVING configuraciones.idconfiguracion>0";
			$cmd->texto.=" ORDER BY configuraciones.descripcion";
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$cmd->texto="SELECT COUNT(*) AS cuenta,configuraciones.descripcion,configuraciones.idconfiguracion FROM gruposordenadores";
			$cmd->texto.=" INNER JOIN ordenadores ON gruposordenadores.idgrupo = ordenadores.grupoid";
			$cmd->texto.=" INNER JOIN configuraciones ON ordenadores.idconfiguracion = configuraciones.idconfiguracion";
			$cmd->texto.=" WHERE (gruposordenadores.idgrupo = ".$idambito.") AND configuraciones.idconfiguracion>0";
			$cmd->texto.=" GROUP BY configuraciones.descripcion, configuraciones.idconfiguracion";
			$cmd->texto.=" HAVING configuraciones.idconfiguracion>0";
			$cmd->texto.=" ORDER BY configuraciones.descripcion";
			break;
		case $AMBITO_ORDENADORES :
			$cmd->texto="SELECT COUNT(*) AS cuenta,configuraciones.descripcion,configuraciones.idconfiguracion FROM ordenadores";
			$cmd->texto.=" INNER JOIN configuraciones ON ordenadores.idconfiguracion = configuraciones.idconfiguracion";
			$cmd->texto.=" WHERE ordenadores.idordenador = ".$idambito;
			$cmd->texto.=" GROUP BY configuraciones.descripcion, configuraciones.idconfiguracion";
			$cmd->texto.=" HAVING configuraciones.idconfiguracion>0";
			$cmd->texto.=" ORDER BY configuraciones.descripcion";
			break;
	}
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	if(!$rs->EOF){
		if($numorde!=$rs->campos["cuenta"]){ // El numero de ordenadores del aula no coincide con los que tienen el mismo perfil hardware
			while (!$rs->EOF){
				if($idconfiguracion!=$rs->campos["idconfiguracion"]){
					if($idconfiguracion!=0) $tablaHtml.="</TABLE>";
					$tablaHtml.= '<TABLE  align=center border=0 cellPadding=1 cellSpacing=1'; 
					$descripcion=$rs->campos["descripcion"];
					$tablaHtml.= "<TR>";
					$tablaHtml.= '<TD align=center ><IMG  style="cursor:hand" oncontextmenu="resalta(this,'.$rs->campos["idconfiguracion"].')" src="../images/iconos/configuraciones.gif">';
					$tablaHtml.='&nbsp;&nbsp<span style="COLOR: #000000;FONT-FAMILY: Verdana;FONT-SIZE: 12px; "><U><b>Configuration</b>&nbsp;'.$rs->campos["descripcion"].'</U></SPAN></TD>';
					$tablaHtml.= "</TR>";
				}
				$tablaHtml.= '<TR><TD>';
				$tablaHtml.=PintaOrdenadores($cmd,$idambito,$ambito,$rs->campos["idconfiguracion"],$rs->campos["cuenta"]);
				$tablaHtml.= '</TD></TR>';
				$tablaHtml.= '<TR><TD>';
				$tablaHtml.=tabla_particiones($cmd,$idcentro,$idambito,$rs->campos["idconfiguracion"],$rs->campos["cuenta"]);
				$tablaHtml.= '</TD></TR>';
				$rs->Siguiente();
			}
			$tablaHtml.="</TABLE>";
		}
		else{
			$tablaHtml.=tabla_particiones($cmd,$idcentro,$idambito,$rs->campos["idconfiguracion"],$rs->campos["cuenta"]);
			$tablaHtml.='<INPUT type=hidden name="nuevasipes" id="ipes_'.$rs->campos["idconfiguracion"].'" value="'.$cadenaip.'">';
		}
	}
	echo $tablaHtml;
	$rs->Cerrar();
}
//________________________________________________________________________________________________________
function PintaOrdenadores($cmd,$idambito,$ambito,$idconfiguracion){
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	$ipidpidc="";
	$rs=new Recordset; 
	$contor=0;
	$maxcontor=10;
	switch($ambito){
		case $AMBITO_AULAS :
			$cmd->texto=" SELECT nombreordenador,ip FROM ordenadores WHERE  idconfiguracion=".$idconfiguracion." AND idaula=".$idambito." ORDER BY nombreordenador";
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$cmd->texto=" SELECT nombreordenador,ip FROM ordenadores WHERE  idconfiguracion=".$idconfiguracion." AND grupoid=".$idambito." ORDER BY nombreordenador";
			break;
	}
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	$tablaHtml='<TABLE align=center border=0><TR>';
	while (!$rs->EOF){
		$contor++;
		$tablaHtml.= '<TD align=center style="FONT-FAMILY: Arial, Helvetica, sans-serif;FONT-SIZE: 8px"><br><IMG src="../images/iconos/ordenador.gif"><br><span style="FONT-SIZE:9px" >'.$rs->campos["nombreordenador"].'</TD>';
		if($contor>$maxcontor){
			$contor=0;
			$tablaHtml.='</TR><TR>';
		}
		$ipidpidc.=$rs->campos["ip"].";";
		$rs->Siguiente();
	}
	$ipidpidc=	substr($ipidpidc,0,strlen($ipidpidc)-1); // Quita la coma
	$tablaHtml.='</TR>';
	$tablaHtml.= '</TR></TABLE>';
	$tablaHtml.='<INPUT type=hidden name="nuevasipes" id="ipes_'.$idconfiguracion.'" value="'.$ipidpidc.'">';
	return($tablaHtml);
}
//________________________________________________________________________________________________________
function tabla_particiones($cmd,$idcentro,$idambito,$idconfiguracion,$cuenta){
	global $tbconfigur;
	global $TbMsg;
	$tablaHtml="";
	$configuracion="";
	$rs=new Recordset; 
	$cmd->texto="SELECT idconfiguracion, configuracion FROM configuraciones WHERE idconfiguracion=".$idconfiguracion;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	if(!$rs->EOF)
		$idc=$rs->campos["idconfiguracion"];
		$configuracion=$rs->campos["configuracion"];
	$rs->Cerrar();
	$auxsplit=split("\t",$configuracion);
	$tablaHtml.= '<TABLE align=center  id=tabla_contenidoparticion_'.$idc.'  value=0><TR><TD>';
	$tablaHtml.= '<TABLE id=tb_particiones_'.$idc.' class=tabla_listados_sin  align=center value=0 cellPadding=1 cellSpacing=1 >';
	$tablaHtml.= '<TR>';
	$tablaHtml.= '<TH align=center ><IMG src="../images/iconos/eliminar.gif"></TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[8].'&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[9].'&nbsp</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[10].'&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[11].'&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[12].'&nbsp;</TH>';
	$tablaHtml.= '</TR>';
	$ultpa=0;
	for($j=0;$j<sizeof($auxsplit)-1;$j++){
		$ValorParametros=extrae_parametros($auxsplit[$j],chr(10),'=');
		$particion=$ValorParametros["numpart"]; // Toma la partici�
		$p=$particion;
		$tipopart=$ValorParametros["tipopart"]; // Toma tama� la partici�
		$tamapart=$ValorParametros["tamapart"]; // Toma tama� la partici�
		$nombreso=$ValorParametros["nombreso"]; // Toma nombre del sistema operativo
		$tiposo=$ValorParametros["tiposo"];
		$valocul=0;
		$codpar=0;
		switch($tipopart){
					case "EMPTY": 
						$codpar=0;
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red"> Espacio sin particionar !!</span>';
						break;
					case "EXT": 
						$codpar=0;
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red"> Partici� extendida !!</span>';
						break;
					case "BIGDOS": 
						$codpar=1;
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Msdos</span>';
						break;
					case "FAT32":
						$codpar=2;
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Windows 98, Millenium</span>';
						break;
					case "HFAT32":
						$codpar=2;
						$valocul=2;
						if(empty($tiposo))
								$nombreso='<span style="COLOR:red">Windows 98, Millenium<span style="COLOR:green;font-weight:600">&nbsp;('.$TbMsg[7].')</span></span>';
						else
								$nombreso.='<span style="COLOR:green;font-weight:600">&nbsp;(Partici� oculta)</span>';
						break;
					case "NTFS":
						$codpar=3;
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Windows XP, Windows 2000, Windows 2003</span>';
						break;
					case "HNTFS":
						$codpar=3;
						$valocul=2;
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Windows XP, Windows 2000, Windows 2003<span style="COLOR:green;font-weight:600">&nbsp;('.$TbMsg[7].')</span></span>';
						else
							$nombreso.='<span style="COLOR:green;font-weight:600">&nbsp;('.$TbMsg[7].')</span>';
						break;
					case "EXT2":
						$codpar=4;
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Linux</span>';
						break;
					case "EXT3":
						$codpar=5;
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Linux</span>';
						break;
					case "EXT4":
						$codpar=6;
						if(empty($tiposo))
							$nombreso='<span style="COLOR:red">Linux</span>';
						break;
					case "LINUX-SWAP": 
						$codpar=7;
						$nombreso='<span style="COLOR:blue">Linux-swap</span>';
						break;
					case "CACHE": 
						$codpar=8;
						$nombreso='<span style="COLOR:blue">CACHE</span>';
						break;
		}
		$ultpa=$p; // Valor de la ultima particion de esa configuraci�
		$tablaHtml.='<TR id=TRparticion_'.$p."_".$idc.'>'.chr(13);
		$tablaHtml.='<TD><input type=checkbox onclick="elimina_particion(this,'.$idc.')" id=eliminarparticion_'.$p."_".$idc.' value=0></TD>'.chr(13);
		$opciones="";
		for($i=1;$i<8;$i++){
			 $opciones.=$i."=".$i.chr(13);
		}
		$opciones.="8=8";
		$tablaHtml.='<TD>'.HTMLCTESELECT($opciones,"numpar_".$p."_".$idc,"estilodesple","",$p,35,"chgpar").'</TD>'.chr(13);
		$opciones="1=BIGDOS".chr(13);
		$opciones.="2=FAT32".chr(13);
		$opciones.="3=NTFS".chr(13);
		$opciones.="4=EXT2".chr(13);
		$opciones.="5=EXT3".chr(13);
		$opciones.="6=EXT4".chr(13);
		$opciones.="7=LINUX-SWAP".chr(13);
		$opciones.="8=CACHE";

		$tablaHtml.='<TD>'.HTMLCTESELECT($opciones,"tipospar_".$p."_".$idc,"estilodesple","EMPTY",$codpar,100,"chgtipopar").'</TD>'.chr(13);
		$tablaHtml.='<TD><span id=tiposo_'.$p."_".$idc.' value=0>&nbsp;'.$nombreso.'&nbsp;</span></TD>'.chr(13);
		$tablaHtml.='<TD align=center>&nbsp<input type=text onchange="chgtama('.$idc.')" id="tamano_'.$p."_".$idc.'" style="width=70" value='.$tamapart.' >&nbsp</TD>'.chr(13);
		$opciones="1=".$TbMsg[14]."".chr(13);
		$opciones.="2=".$TbMsg[15]."".chr(13);
		$opciones.="3=".$TbMsg[16]."";
		$tablaHtml.='<TD>&nbsp'.HTMLCTESELECT($opciones,"acciones_".$p."_".$idc,"estilodesple",$TbMsg[13],$valocul,100,"chgaccion").'&nbsp</TD>'.chr(13);
		$tablaHtml.='</TR>'.chr(13);
	}
	$tablaHtml.='</TABLE>';
	$tablaHtml.= '</TD></TR></TABLE>';

  // Boton de insercion
	$tablaHtml.= '<INPUT type=hidden id="ultpa_'.$idc.'" value='.$ultpa.'>';
	$tablaHtml.= '<div align=center>';
	$tablaHtml.= '<br><A href="#boton_add"><IMG border=0 name="btanade_"'.$idc.'  src="../images/boton_annadir.gif" onclick="annadir_particion('.$idc.')" WIDTH="73" HEIGHT="22"></A>';
	$tablaHtml.= '</div><br>';
	$tbconfigur.=$idc.";";
	return($tablaHtml);
}
//________________________________________________________________________________________________________
//	Crea la patron de linea de la tabla Particiones
//________________________________________________________________________________________________________
function Patrontabla_Particion(){
	global $TbMsg;
	$p="_upa_";
	$idc="_cfg_";
	$tablaHtml='<TR id=TRparticion_'.$p."_".$idc.'>'.chr(13);
	$tablaHtml.='<TD><input type=checkbox onclick="elimina_particion(this,'.$idc.')" id=eliminarparticion_'.$p."_".$idc.' value=0></TD>'.chr(13);
	$opciones="";
		for($i=1;$i<8;$i++){
			$opciones.=$i."=".$i.chr(13);
		}
		$opciones.="8=8";
		$tablaHtml.='<TD>'.HTMLCTESELECT($opciones,"numpar_".$p."_".$idc,"estilodesple","",$p,35,"chgpar").'</TD>'.chr(13);
		$opciones="1=BIGDOS".chr(13);
		$opciones.="2=FAT32".chr(13);
		$opciones.="3=NTFS".chr(13);
		$opciones.="4=EXT2".chr(13);
		$opciones.="5=EXT3".chr(13);
		$opciones.="6=EXT4".chr(13);
		$opciones.="7=LINUX-SWAP";
		$tablaHtml.='<TD>'.HTMLCTESELECT($opciones,"tipospar_".$p."_".$idc,"estilodesple","EMPTY",0,100,"chgtipopar").'</TD>'.chr(13);
		$tablaHtml.='<TD><span id=tiposo_'.$p."_".$idc.' value=0>&nbsp;<span style="COLOR:red">'.$TbMsg[17].'</span>&nbsp;</span></TD>'.chr(13);
		$tablaHtml.='<TD align=center>&nbsp<input type=text onchange="chgtama('.$idc.')" id="tamano_'.$p."_".$idc.'" style="width=70" value=0 >&nbsp</TD>'.chr(13);
		$opciones="1=".$TbMsg[14]."".chr(13);
		$opciones.="2=".$TbMsg[15]."".chr(13);
		$opciones.="3=".$TbMsg[16]."";
		$tablaHtml.='<TD>'.HTMLCTESELECT($opciones,"acciones_".$p."_".$idc,"estilodesple",$TbMsg[13],0,100,"chgaccion").'&nbsp</TD>'.chr(13);
		$tablaHtml.='</TR>'.chr(13);
		//$tablaHtml.='</TABLE>';
		//$tablaHtml.= '</TD></TR></TABLE>';
		return($tablaHtml);
}
?>