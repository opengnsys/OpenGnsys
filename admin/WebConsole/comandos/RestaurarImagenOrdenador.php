<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: RestaurarImagenOrdenador.php
// Descripción : 
//		Implementación del comando "RestaurarImagen" (Ordenadores)
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../idiomas/php/".$idioma."/comandos/restaurarimagen_".$idioma.".php");
//________________________________________________________________________________________________________
$identificador=0;
$nombrefuncion="";
$ejecutor="";
$tipotrama=""; 
$ambito=0; 
$idambito=0;
$nombreambito="";
$cadenaip="";
$cadenamac="";

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
$cadenamac=$ValorParametros["cadenamac"]; 
//________________________________________________________________________________________________________
$idordenador=$idambito; 
$nombreordenador="";
$ip=$cadenaip;
$mac=$cadenamac;
$idperfilhard=0;
$idservidordhcp=0;
$idservidorrembo=0;

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexióncon servidor B.D.
$resul=toma_propiedades($cmd,$idordenador);
if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperaci� de datos.
//___________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<STYLE TYPE="text/css"></STYLE>
<SCRIPT language="javascript" src="./jscripts/RestaurarImagenOrdenador.js"></SCRIPT>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/restaurarimagen_'.$idioma.'.js"></SCRIPT>'?>
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
echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'</span><br>';
echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras><U>'.$TbMsg[6].': '.$textambito.','.$nombreambito.'</U></span>&nbsp;&nbsp;</span></p>';
?>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
<BR>
<P align=center><SPAN align=center class=subcabeceras><? echo $TbMsg[7]?></SPAN>
<BR>
<FORM  name="fdatos"> 
	<?echo tabla_particiones($cmd,$idcentro,$idambito);?>
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
//*************************************************************************************************************************************************
function tabla_particiones($cmd,$idcentro,$idordenador){
	global $TbMsg;
	$tablaHtml="";
	$rs=new Recordset; 
	$rsp=new Recordset; 
	$cmd->texto="SELECT particiones.particion FROM particiones INNER JOIN ordenadores ON particiones.idparticion=ordenadores.idparticion WHERE ordenadores.idordenador='".$idordenador."'";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	if ($rs->EOF) return($tablaHtml);
	$particion=$rs->campos["particion"];
	$tablaHtml.= '<TABLE  class=tabla_listados_sin  align=center border=0 cellPadding=1 cellSpacing=1 >';
	$tablaHtml.= '<TR>';
	$tablaHtml.= '<TH align=center>&nbsp;&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;P&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;'.$TbMsg[9].'&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;Path&nbsp;</TH>';
	$tablaHtml.= '<TH align=center>&nbsp;Repositorios centralizados&nbsp;</TH>';
	//$tablaHtml.= '<TH colspan=4 align=center>&nbsp;'.$TbMsg[9].'&nbsp;</TH>';
	$tablaHtml.= '</TR>';
	$auxsplit=split(";",$particion);

	for($j=0;$j<sizeof($auxsplit)-1;$j++){
		$dual=split("=",$auxsplit[$j]);
		$particion=$dual[0]; // Toma la partici�
		$tipopart=$dual[1]; // Toma la partici�

		if($tipopart== "EMPTY" ||  $tipopart== "LINUX-SWAP") continue;

		$tablaHtml.='<TR >'.chr(13);
		// selector checkbox
		$tablaHtml.='<TD ><input onclick=seleccionar("'.$particion.'") type=checkbox name=particion_'.$particion.' value='.$particion.'></TD>'.chr(13);
		// partición
		$tablaHtml.='<TD ><b>&nbsp;'.$particion.'&nbsp;</b></TD>'.chr(13);
		//$tablaHtml.='<TD  align=center><b>&nbsp;('.$tipopart.") - </b>".$TbMsg[10].'</TD>';

		$idimagen=TomaImagen($cmd,$idordenador,$particion);
		//imagen a elegir
		$tablaHtml.='<TD  align=cente>'.HTMLSELECT_Imagendis($cmd,$idcentro,$tipopart,$particion,true,$idimagen,$idordenador).'</TD>';

		//path
		$parametros="0=".chr(13);
		$parametros.="1=cache".chr(13);
		$parametros.="2=repositorio";
		$tablaHtml.= '<TD>'.HTMLCTESELECT($parametros,"pathrmb_".$particion,"estilodesple","",0,60).'</TD>';


		//Clonación
		$metodos="UNICAST=UNICAST".chr(13);
		$metodos.="MULTICAST=MULTICAST".chr(13);
		$metodos.="TORRENT=TORRENT";
		$tablaHtml.='<TD>'.HTMLCTESELECT($metodos,"protoclonacion_".$particion,"estilodesple","",$_SESSION["protclonacion"],150).'</TD>';
					
		//$tablaHtml.='<TD align=center><b>&nbsp;('.$tipopart.") -</b> ".$TbMsg[11].'</TD>';
		$tablaHtml.='</TR>'.chr(13);



	/*
		$tablaHtml.='<TR>'.chr(13);
		$tablaHtml.='<TD></TD>'.chr(13);
		$tablaHtml.='<TD></TD>'.chr(13);
		$tablaHtml.='<TD  align=cente>'.HTMLSELECT_Imagendis($cmd,$idcentro,$tipopart,$particion,true,$idimagen,$idordenador).'</TD>';

		$parametros="0=".chr(13);
		$parametros.="1=cache".chr(13);
		$parametros.="2=net";
		$tablaHtml.= '<TD>'.HTMLCTESELECT($parametros,"pathrmb_".$particion,"estilodesple","",0,60).'</TD>';
	
		$tablaHtml.='<TD  align=cente>'.HTMLSELECT_Imagendis($cmd,$idcentro,$tipopart,$particion,false,$idimagen,$idordenador).'</TD>';
		$tablaHtml.='</TR>'.chr(13);
	*/
	}
	$tablaHtml.='</TABLE>';
	$tablaHtml.='<BR>';
	return($tablaHtml);
}
/*________________________________________________________________________________________________________
	Crea los desplegables de las imagenes disponibles para la particiones
________________________________________________________________________________________________________*/
function HTMLSELECT_Imagendis($cmd,$idcentro,$tipopart,$particion,$miso,$idimagen,$idordenador){
	$SelectHtml="";
	$rs=new Recordset; 
	$cmd->texto="SELECT perfilessoft.idperfilsoft,ordenadores.idperfilhard,imagenes.descripcion,imagenes.idimagen,tiposos.tipopar,tiposos.nemonico FROM ordenadores";
	$cmd->texto.=" INNER JOIN perfileshard ON ordenadores.idperfilhard = perfileshard.idperfilhard";
	$cmd->texto.=" INNER JOIN perfileshard_perfilessoft ON perfileshard.idperfilhard = perfileshard_perfilessoft.idperfilhard";
	$cmd->texto.=" INNER JOIN perfilessoft ON perfileshard_perfilessoft.idperfilsoft = perfilessoft.idperfilsoft";
	$cmd->texto.=" INNER JOIN imagenes ON perfilessoft.idperfilsoft = imagenes.idperfilsoft";
	$cmd->texto.=" INNER JOIN perfilessoft_softwares ON perfilessoft.idperfilsoft = perfilessoft_softwares.idperfilsoft";
	$cmd->texto.=" INNER JOIN softwares ON perfilessoft_softwares.idsoftware = softwares.idsoftware";
	$cmd->texto.=" INNER JOIN tiposos ON softwares.idtiposo = tiposos.idtiposo";
	$cmd->texto.=" WHERE imagenes.idcentro=".$idcentro." AND ordenadores.idordenador='".$idordenador."'";

  $swo=substr ($tipopart,0,1);
  if($swo=="H") 
	 $tipopart=substr ($tipopart,1,strlen($tipopart)-1);

	$sufi="";
	if($miso){
		$cmd->texto.=" AND (tiposos.tipopar = '".$tipopart."'  OR tiposos.tipopar ='H".$tipopart."' )";
		$sufi="M"; // Mismo sistema
		}
	else{
		$cmd->texto.=" AND (tiposos.tipopar <> '".$tipopart."' AND tiposos.tipopar <> 'H".$tipopart."')";
		$sufi="O"; // Otro sistema
	}

	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$SelectHtml.= '<SELECT onchange="marcar(this,'.$particion.')" class="formulariodatos" id="desple_'.$sufi."_".$particion.'" style="WIDTH: 250">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';
	$rs->Primero(); 
	while (!$rs->EOF){
		if(CuestionIncrementales($cmd,$rs->campos["idperfilhard"],$rs->campos["idperfilsoft"],$rs->campos["idimagen"])){
			$SelectHtml.='<OPTION value="'.$rs->campos["idimagen"]."_".$rs->campos["idperfilhard"]."_".$rs->campos["idperfilsoft"]."_".$rs->campos["tipopar"]."_".$rs->campos["nemonico"].'"';
			if($idimagen==$rs->campos["idimagen"]) $SelectHtml.= " selected ";
			$SelectHtml.=">".$rs->campos["descripcion"].'</OPTION>';
		}
		$rs->Siguiente();
	}
	$SelectHtml.= '</SELECT>';
	$rs->Cerrar();
	return($SelectHtml);
}
//________________________________________________________________________________________________________
//	Comprueba que la imagen no tiene incrementales o si la tiene que existen para el perfil hardware del ordenador
//________________________________________________________________________________________________________
function CuestionIncrementales($cmd,$idperfilhard,$idperfilsoft,$idimagen){
	$wrs=new Recordset; 
	$cmd->texto=" SELECT idsoftincremental FROM imagenes_softincremental WHERE idimagen=".$idimagen;
	$wrs->Comando=&$cmd; 
	if (!$wrs->Abrir()) return(false); // Error al abrir recordset
	if ($wrs->numeroderegistros==0) return(true);
	while (!$wrs->EOF){
		if(!ExisteIncremental($cmd,$idperfilhard,$idperfilsoft ,$wrs->campos["idsoftincremental"])) return(false);
		$wrs->Siguiente();
	}
	return(true);
}
//________________________________________________________________________________________________________
//	Comprueba que existe una incremental para cierta combinaci� de perfil software y perfil hardware
//________________________________________________________________________________________________________
function ExisteIncremental($cmd,$idperfilhard,$idperfilsoft ,$idsoftincremental){
	$rs=new Recordset; 
	$cmd->texto="SELECT  COUNT(*) as contador FROM perfileshard_perfilessoft INNER JOIN phard_psoft_softincremental ON perfileshard_perfilessoft.idphardidpsoft = phard_psoft_softincremental.idphardidpsoft WHERE (perfileshard_perfilessoft.idperfilhard = ".$idperfilhard.") AND (perfileshard_perfilessoft.idperfilsoft = ".$idperfilsoft.") AND (phard_psoft_softincremental.idsoftincremental = ".$idsoftincremental.")";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	if ($rs->campos["contador"]==0) return(false);
	return(true);
}
/*________________________________________________________________________________________________________
	Recupera los datos de un ordenador
		Parametros: 
		- cmd: Una comando ya operativo (con conexiónabierta)  
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
	Toma el identificador de la imagen
________________________________________________________________________________________________________*/
function TomaImagen($cmd,$idordenador,$particion){
	$rs=new Recordset; 
	$cmd->texto="SELECT imagenes.idimagen FROM ordenador_imagen";
	$cmd->texto.=" INNER JOIN imagenes ON ordenador_imagen.idimagen = imagenes.idimagen ";
	$cmd->texto.=" INNER JOIN ordenadores ON ordenador_imagen.idordenador = ordenadores.idordenador ";
	$cmd->texto.=" WHERE ordenadores.idordenador ='".$idordenador."' AND ordenador_imagen.particion = ".$particion;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	$idimagen=0;
	if(!$rs->EOF)
			$idimagen=$rs->campos["idimagen"];
	$rs->Cerrar();
	return($idimagen);
}
?>
