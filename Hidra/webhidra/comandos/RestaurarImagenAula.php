<?
// *************************************************************************************************************************************************
// Aplicaci� WEB: Hidra
// Copyright 2003-2005  Jos�Manuel Alonso. Todos los derechos reservados.
// Fecha Creaci�: A� 2003-2004
// Fecha �tima modificaci�: Marzo-2005
// Nombre del fichero: RestaurarImagenAula.php
// Descripci� : 
//		Implementaci� del comando "RestaurarImagen" (Aula)
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
//________________________________________________________________________________________________________
$idaula=$idambito; 
$nombreaula="";
$urlfoto="";
$cagnon=false;
$pizarra=false;
$ubicacion="";
$comentarios="";
$ordenadores=0;
$puestos=0;
$grupoid=0;

$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexi� con servidor B.D.
$resul=toma_propiedades($cmd,$idaula);
if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperaci� de datos.
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administraci� web de aulas</TITLE>
<HEAD>
<LINK rel="stylesheet" type="text/css" href="../hidra.css">
<SCRIPT language="javascript" src="./jscripts/RestaurarImagenAula.js"></SCRIPT>
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
<BR><BR>
<FORM  name="fdatos"> 
		<? echo tabla_imagenes($cmd,$idcentro,$idambito);	?>
</FORM>
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
//*************************************************************************************************************************************************
function tabla_imagenes($cmd,$idcentro,$idaula){
	global $cadenaip;
	$tablaHtml="";
	$rs=new Recordset; 
	$numorde=0;
	$cmd->texto="SELECT COUNT(*) AS numorde FROM ordenadores WHERE idaula=".$idaula;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	if(!$rs->EOF)
		$numorde=$rs->campos["numorde"];
	$descripcion="";
	$cmd->texto="SELECT COUNT(*) AS cuenta,perfileshard.descripcion,perfileshard.idperfilhard, ordenadores.idparticion FROM aulas";
	$cmd->texto.=" INNER JOIN ordenadores ON aulas.idaula = ordenadores.idaula";
	$cmd->texto.=" INNER JOIN perfileshard ON ordenadores.idperfilhard = perfileshard.idperfilhard";
	$cmd->texto.=" WHERE (aulas.idaula = ".$idaula.")  AND idparticion>0";
	$cmd->texto.=" GROUP BY perfileshard.descripcion,perfileshard.idperfilhard,ordenadores.idparticion";
	$cmd->texto.=" ORDER BY perfileshard.descripcion";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	if(!$rs->EOF){
		if($numorde!=$rs->campos["cuenta"]){ // El numero de ordenadores del aula no coincide con los que tienen el mismo perfil hardware
			while (!$rs->EOF){
				if($descripcion!=$rs->campos["descripcion"]){
					if($descripcion!="")
						$tablaHtml.="</TABLE><br><br>";
					$tablaHtml.= '<TABLE  align=center border=0 cellPadding=1 cellSpacing=1'; 
					$descripcion=$rs->campos["descripcion"];
					$tablaHtml.= "<TR>";
					$tablaHtml.= '<TD align=center ><IMG  src="../images/iconos/perfilhardware.gif">';
					$tablaHtml.='<span style="COLOR: #000000;FONT-FAMILY: Verdana;FONT-SIZE: 12px; "><U><b>&nbsp;Perfil Hardware:</b>&nbsp;'.$rs->campos["descripcion"].'</U></SPAN></TD>';
					$tablaHtml.= "</TR>";
				}
				$tablaHtml.= '<TR><TD align=center>';
				$tablaHtml.=PintaOrdenadores($cmd,$idaula,$rs->campos["idperfilhard"],$rs->campos["idparticion"],$rs->campos["cuenta"]);
				$tablaHtml.= '</TD></TR>';
				$tablaHtml.= '<TR><TD>';
				$tablaHtml.=tabla_particiones($cmd,$idcentro,$idaula,$rs->campos["idperfilhard"],$rs->campos["idparticion"],$rs->campos["cuenta"]);
				$tablaHtml.= '</TD></TR>';
				$rs->Siguiente();
			}
			$tablaHtml.="</TABLE>";
		}
		else{
			$tablaHtml.=tabla_particiones($cmd,$idcentro,$idaula,$rs->campos["idperfilhard"],$rs->campos["idparticion"],$rs->campos["cuenta"]);
			$tablaHtml.='<INPUT type=hidden name="nuevasipes" id="ipes_'.$rs->campos["idperfilhard"].'_'.$rs->campos["idparticion"].'" value="'.$cadenaip.'">';
		}
	}
	echo $tablaHtml;
	$rs->Cerrar();
}
/*________________________________________________________________________________________________________
	Crea los desplegables de las imagenes disponibles para la particiones
________________________________________________________________________________________________________*/
function HTMLSELECT_Imagendis($cmd,$idcentro,$tipopart,$particion,$miso,$idimagen,$idaula,$idperfilhard,$idparticion,$cuenta){
	$SelectHtml="";
	$rs=new Recordset; 
	$cmd->texto="SELECT COUNT(*) AS contador, perfilessoft.idperfilsoft, imagenes.descripcion, imagenes.idimagen,tiposos.tipopar,tiposos.nemonico FROM ordenadores";
	$cmd->texto.=" INNER JOIN perfileshard ON ordenadores.idperfilhard = perfileshard.idperfilhard"; 
	$cmd->texto.=" INNER JOIN perfileshard_perfilessoft ON perfileshard.idperfilhard = perfileshard_perfilessoft.idperfilhard";
	$cmd->texto.=" INNER JOIN perfilessoft ON perfileshard_perfilessoft.idperfilsoft = perfilessoft.idperfilsoft";
	$cmd->texto.=" INNER JOIN imagenes ON perfilessoft.idperfilsoft = imagenes.idperfilsoft";
	$cmd->texto.=" INNER JOIN perfilessoft_softwares ON perfilessoft.idperfilsoft = perfilessoft_softwares.idperfilsoft";
	$cmd->texto.=" INNER JOIN softwares ON perfilessoft_softwares.idsoftware = softwares.idsoftware";
	$cmd->texto.=" INNER JOIN tiposos ON softwares.idtiposo = tiposos.idtiposo";
	$cmd->texto.=" WHERE (imagenes.idcentro = ".$idcentro.") AND (ordenadores.idaula = ".$idaula.") AND (ordenadores.idperfilhard = ".$idperfilhard.") AND (ordenadores.idparticion=".$idparticion.")";

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
	$cmd->texto.=" GROUP BY perfilessoft.idperfilsoft, imagenes.descripcion, imagenes.idimagen,tiposos.tipopar,tiposos.nemonico ";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$SelectHtml.= '<SELECT onchange="marcar(this,'."'".$particion."_".$idperfilhard."_".$idparticion."'".')" class="formulariodatos" id="desple_'.$sufi."_".$particion."_".$idperfilhard."_".$idparticion.'" style="WIDTH: 250">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';
	$rs->Primero(); 
	while (!$rs->EOF){
		if($rs->campos["contador"]==$cuenta){
			if(CuestionIncrementales($cmd,$idperfilhard,$rs->campos["idperfilsoft"],$rs->campos["idimagen"])){
				$SelectHtml.='<OPTION value="'.$rs->campos["idimagen"]."_".$idperfilhard."_".$rs->campos["idperfilsoft"]."_".$rs->campos["tipopar"]."_".$rs->campos["nemonico"].'"';
				if($idimagen==$rs->campos["idimagen"]) $SelectHtml.= " selected ";
				$SelectHtml.=">".$rs->campos["descripcion"].'</OPTION>';
			}
		}
		$rs->Siguiente();
	}
	$SelectHtml.= '</SELECT>';
	$rs->Cerrar();
	return($SelectHtml);
}
/*________________________________________________________________________________________________________
	Crea la tabla de ordenadores ( iconos peque�s cuando en el aula no hay uniformidad
________________________________________________________________________________________________________*/
function PintaOrdenadores($cmd,$idaula,$idperfilhard,$idparticion){
	$ipidpidc="";
	$rs=new Recordset; 
	$contor=0;
	$maxcontor=10;
	$cmd->texto=" SELECT nombreordenador,ip FROM ordenadores WHERE idperfilhard=".$idperfilhard." AND idparticion=".$idparticion." AND idaula=".$idaula." ORDER BY nombreordenador";
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
	$tablaHtml.='<INPUT type=hidden name="nuevasipes" id="ipes_'.$idperfilhard.'_'.$idparticion.'" value="'.$ipidpidc.'">';
	return($tablaHtml);
}
/*________________________________________________________________________________________________________
	Crea la tabla de particiones
________________________________________________________________________________________________________*/
function tabla_particiones($cmd,$idcentro,$idaula,$idperfilhard,$idparticion,$cuenta){
	global $TbMsg;
	$tablaHtml="";
	$particion="";
	$rs=new Recordset; 
	$cmd->texto="SELECT particion FROM particiones WHERE idparticion=".$idparticion;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	if(!$rs->EOF)
		$particion=$rs->campos["particion"];
	$rs->Cerrar();
	$tablaHtml.= '<TABLE  class=tabla_listados_sin  align=center border=0 cellPadding=1 cellSpacing=1>';
	$tablaHtml.= '<TR>';
	$tablaHtml.= '<TH  align=center>&nbsp;&nbsp;</TH>';
	$tablaHtml.= '<TH  align=center>&nbsp;P&nbsp;</TH>';
	$tablaHtml.= '<TH colspan=3 align=center>&nbsp;'.$TbMsg[9].'&nbsp;</TH>';
	$tablaHtml.= '</TR>';
	$auxsplit=split(";",$particion);
	for($j=0;$j<sizeof($auxsplit)-1;$j++){
		$dual=split("=",$auxsplit[$j]);
		$particion=$dual[0]; // Toma la partici�
		$tipopart=$dual[1]; // Toma la partici�
		if($tipopart== "EMPTY" ||  $tipopart== "LINUX-SWAP") continue;

		$tablaHtml.='<TR >'.chr(13);
		$tablaHtml.='<TD ><input onclick=seleccionar("'.$particion.'_'.$idperfilhard.'_'.$idparticion.'") type=checkbox name=particion_'.$particion.'_'.$idperfilhard.'_'.$idparticion.' value='.$particion.'_'.$idperfilhard.'_'.$idparticion.'></TD>'.chr(13);
		$tablaHtml.='<TD ><b>&nbsp;'.$particion.'&nbsp;</b></TD>'.chr(13);
		$tablaHtml.='<TD  align=center><b>&nbsp;('.$tipopart.") - </b>".$TbMsg[10].'</TD>';
		$tablaHtml.='<TD  align=center>&nbsp;Path</TD>';
		$tablaHtml.='<TD   align=center><b>&nbsp;('.$tipopart.") -</b> ".$TbMsg[11].'</TD>';
		$tablaHtml.='</TR>'.chr(13);

		$idimagen=TomaImagen($cmd,$idaula,$idperfilhard,$idparticion,$particion,$cuenta);

		$tablaHtml.='<TR>'.chr(13);
		$tablaHtml.='<TD></TD>'.chr(13);
		$tablaHtml.='<TD></TD>'.chr(13);
		$tablaHtml.='<TD  align=center>'. HTMLSELECT_Imagendis($cmd,$idcentro,$tipopart,$particion,true,$idimagen,$idaula,$idperfilhard,$idparticion,$cuenta).'</TD>';

		$parametros="0=".chr(13);
		$parametros.="1=cache".chr(13);
		$parametros.="2=net";
		$tablaHtml.= '<TD>'.HTMLCTESELECT($parametros, "pathrmb_".$particion.'_'.$idperfilhard.'_'.$idparticion,"estilodesple","",0,60).'</TD>';

		$tablaHtml.='<TD  align=center>'. HTMLSELECT_Imagendis($cmd,$idcentro,$tipopart,$particion,false,$idimagen,$idaula,$idperfilhard,$idparticion,$cuenta).'</TD>';
		$tablaHtml.='</TR>'.chr(13);
	}
	$tablaHtml.='</TABLE>';
	$tablaHtml.='<BR>';
	return($tablaHtml);
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
	Recupera los datos de un aula
		Parametros: 
		- cmd:Una comando ya operativo (con conexi� abierta)  
		- ida:El identificador del aula
________________________________________________________________________________________________________*/
function toma_propiedades($cmd,$ida){
	global $nombreaula;
	global $urlfoto;
	global $cagnon;
	global $pizarra;
	global $ubicacion;
	global $comentarios;
	global $ordenadores;
	global $puestos;
	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM aulas WHERE idaula=".$ida;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(false); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombreaula=$rs->campos["nombreaula"];
		$urlfoto=$rs->campos["urlfoto"];
		$cagnon=$rs->campos["cagnon"];
		$pizarra=$rs->campos["pizarra"];
		$ubicacion=$rs->campos["ubicacion"];
		$comentarios=$rs->campos["comentarios"];
		$puestos=$rs->campos["puestos"];
		$rs->Cerrar();
		$cmd->texto="SELECT count(*) as numordenadores FROM ordenadores WHERE idaula=".$ida;
		$rs->Comando=&$cmd; 
		if (!$rs->Abrir()) return(false); // Error al abrir recordset
		$rs->Primero(); 
		if (!$rs->EOF)
			$ordenadores=$rs->campos["numordenadores"];
		return(true);
	}
	else
		return(false);
}
/*________________________________________________________________________________________________________
	Toma el identificador de la imagen
________________________________________________________________________________________________________*/
function TomaImagen($cmd,$idaula,$idperfilhard,$idparticion,$particion,$cuenta){
	$rs=new Recordset; 
	$cmd->texto="SELECT COUNT(*) AS contador,  imagenes.idimagen FROM ordenadores INNER JOIN ordenador_imagen ON ordenadores.idordenador = ordenador_imagen.idordenador INNER JOIN imagenes ON ordenador_imagen.idimagen = imagenes.idimagen WHERE ordenadores.idperfilhard = ".$idperfilhard." AND ordenadores.idparticion = ".$idparticion." AND ordenadores.idaula =".$idaula." AND ordenador_imagen.particion = ".$particion." GROUP BY imagenes.idimagen" ;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	$idimagen=0;
	if(!$rs->EOF){
		if($rs->campos["contador"]==$cuenta){
			$idimagen=$rs->campos["idimagen"];
		}
	}
	$rs->Cerrar();
	return($idimagen);
}
?>
