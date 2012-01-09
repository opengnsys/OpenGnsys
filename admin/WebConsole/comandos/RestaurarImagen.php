<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
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
include_once("../includes/TomaDato.php");
include_once("../includes/ConfiguracionesParticiones.php");
include_once("../includes/RecopilaIpesMacs.php");
include_once("../idiomas/php/".$idioma."/comandos/restaurarimagen_".$idioma.".php");
//________________________________________________________________________________________________________
include_once("./includes/capturaacciones.php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
//
// Captura parámetros
//________________________________________________________________________________________________________

$ambito=0;
$idambito=0;

// Agrupamiento por defecto
$fk_sysFi=0;
$fk_tamano=0;
$fk_nombreSO=0;

if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if (isset($_GET["ambito"])) $ambito=$_GET["ambito"]; 

if (isset($_POST["idambito"])) $idambito=$_POST["idambito"]; 
if (isset($_POST["ambito"])) $ambito=$_POST["ambito"]; 

if (isset($_POST["fk_sysFi"])) $fk_sysFi=$_POST["fk_sysFi"]; 
if (isset($_POST["fk_tamano"])) $fk_tamano=$_POST["fk_tamano"]; 
if (isset($_POST["fk_nombreSO"])) $fk_nombreSO=$_POST["fk_nombreSO"]; 

//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<STYLE TYPE="text/css"></STYLE>
<SCRIPT language="javascript" src="./jscripts/RestaurarImagen.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/restaurarimagen_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<?
	switch($ambito){
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
	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'</span><br>'; // Cabecera
	echo '<IMG src="'.$urlimg.'">&nbsp;&nbsp;<span align=center class=subcabeceras>
				<U>'.$TbMsg[6].': '.$textambito.','.$nombreambito.'</U></span>&nbsp;&nbsp;</span></p>'; // Subcebecera
	echo '<P align=center><SPAN align=center class=subcabeceras>'.$TbMsg[19].'</SPAN></P>';		
	if($ambito!=$AMBITO_ORDENADORES){	
		$cadenaid="";
		$cadenaip="";
		$cadenamac="";
		RecopilaIpesMacs($cmd,$ambito,$idambito);		
	?>
		<FORM action="RestaurarImagen.php" name="fdatos" method="POST">
				<INPUT type="hidden" name="idambito" value="<? echo $idambito?>">
				<INPUT type="hidden" name="ambito" value="<? echo $ambito?>">	
				<INPUT type="hidden" name="cadenaid" value="<? echo $cadenaid?>">				
				<TABLE class="tabla_busquedas" align=center border=0 cellPadding=0 cellSpacing=0>
				<TR>
					<TH height=15 align="center" colspan=14><? echo $TbMsg[18]?></TH>
				</TR>
				<TR>
					<TD align=right><? echo $TbMsg[30]?></TD>
					<TD align=center><INPUT type="checkbox" value="<? echo $msk_sysFi?>" name="fk_sysFi" <? if($fk_sysFi==$msk_sysFi) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>

					<TD align=right><? echo $TbMsg[32]?></TD>
					<TD align=center><INPUT type="checkbox" value="<? echo $msk_tamano?>" name="fk_tamano" <? if($fk_tamano==$msk_tamano) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>
				
					<TD align=right><? echo $TbMsg[31]?></TD>
					<TD align=center><INPUT type="checkbox" value="<? echo $msk_nombreSO?>" name="fk_nombreSO" <? if($fk_nombreSO==$msk_nombreSO) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>				
				</TR>
				<TR>
					<TD height=2 style="BORDER-TOP:#999999 1px solid;" align="center" colspan=14>&nbsp;</TD>			
				</TR>
				<TR>
					<TD height=20 align="center" colspan=14>
						<A href=#>
						<IMG border=0 src="../images/boton_confirmar.gif" onclick="document.fdatos.submit()"></A></TD>			
				</TR>
			</TABLE>
		</FORM>	
<?
	}
	$sws=$fk_sysFi |  $fk_tamano | $fk_nombreSO;
	pintaConfiguraciones($cmd,$idambito,$ambito,9,$sws,false);	
	//________________________________________________________________________________________________________
	include_once("./includes/formularioacciones.php");
	//________________________________________________________________________________________________________
	//________________________________________________________________________________________________________
	include_once("./includes/opcionesacciones.php");
	//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
//________________________________________________________________________________________________________
//
//	Descripción:
//		(Esta función es llamada por pintaConfiguraciones que está incluida en ConfiguracionesParticiones.php)
//		Crea una taba html con las especificaciones de particiones de un ambito ya sea ordenador,
//		grupo de ordenadores o aula
//	Parametros:
//		$configuraciones: Cadena con las configuraciones de particioners del ámbito. El formato 
//		sería una secuencia de cadenas del tipo "clave de configuración" separados por "@" 
//			Ejemplo:1;7;30000000;3;3;0;@2;130;20000000;5;4;0;@3;131;1000000;0;0;0;0
//	Devuelve:
//		El código html de la tabla
//________________________________________________________________________________________________________
function pintaParticiones($cmd,$configuraciones,$idordenadores,$cc,$ambito,$idambito)
{
	global $tbKeys; // Tabla contenedora de claves de configuración
	global $conKeys; // Contador de claves de configuración
	global $TbMsg;
	global $_SESSION;
	$colums=8;
	echo '<TR>';
	echo '<TH align=center>&nbsp;&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[8].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[24].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[31].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[27].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[22].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[10].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[11].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[9].'&nbsp;</TH>';
	echo '</TR>';

	$auxCfg=split("@",$configuraciones); // Crea lista de particiones
	for($i=0;$i<sizeof($auxCfg);$i++){
		$auxKey=split(";",$auxCfg[$i]); // Toma clave de configuracion
		for($k=0;$k<$conKeys;$k++){ // Busca los literales para las claves de esa partición
			if($tbKeys[$k]["cfg"]==$auxCfg[$i]){ // Claves encontradas
				$swcc=$tbKeys[$k]["clonable"];
				echo '<TR>'.chr(13);
				if($swcc){
					$icp=$cc."_".$tbKeys[$k]["numpar"]; // Identificador de la configuración-partición
					echo '<TD ><input type=radio idcfg="'.$cc.'" id="'.$icp.'" name="particion" value='.$tbKeys[$k]["numpar"].'></TD>'.chr(13);
					echo '<TD align=center>&nbsp;'.$tbKeys[$k]["numpar"].'&nbsp;</TD>'.chr(13);
					echo '<TD align=center>&nbsp;'.$tbKeys[$k]["tipopar"].'&nbsp;</TD>'.chr(13);
					
					//echo '<TD>&nbsp;'.$tbKeys[$k]["nombreso"].'&nbsp;</TD>'.chr(13);
					echo '<TD align=center>&nbsp;'.tomaNombresSO($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);	
					
					//echo'<TD align=center>&nbsp;'.$tbKeys[$k]["sistemafichero"].'&nbsp;</TD>'.chr(13);
					echo'<TD align=center>&nbsp;'.tomaSistemasFicheros($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);

					//echo'<TD align=rigth>&nbsp;'.formatomiles($tbKeys[$k]["tamano"]).'&nbsp;</TD>'.chr(13);
					echo'<TD align=center>&nbsp;'.tomaTamano($tbKeys[$k]["numpar"],$idordenadores).'&nbsp;</TD>'.chr(13);	
									
					echo '<TD>'.HTMLSELECT_imagenes($cmd,$tbKeys[$k]["idimagen"],$tbKeys[$k]["numpar"],$tbKeys[$k]["codpar"],$icp,true,$idordenadores,$ambito).'</TD>';
					echo '<TD>'.HTMLSELECT_imagenes($cmd,$tbKeys[$k]["idimagen"],$tbKeys[$k]["numpar"],$tbKeys[$k]["codpar"],$icp,false,$idordenadores,$ambito).'</TD>';
					//Clonación
					
					$metodos="UNICAST-DIRECT=UNICAST-DIRECT".chr(13);
					$metodos.="MULTICAST-DIRECT " . mcast_syntax($cmd,$ambito,$idambito) . "=MULTICAST-DIRECT".chr(13);
					$metodos.="MULTICAST " . mcast_syntax($cmd,$ambito,$idambito) . "=MULTICAST-CACHE".chr(13);
					$metodos.="TORRENT peer:60=TORRENT";
					
					$TBmetodos["UNICAST-DIRECT"]=1;
					$TBmetodos["MULTICAST-DIRECT"]=2;
					$TBmetodos["MULTICAST-CACHE"]=3;
					$TBmetodos["TORRENT"]=4;
					
					$idxc=$_SESSION["protclonacion"];
					echo '<TD>'.HTMLCTESELECT($metodos,"protoclonacion_".$icp,"estilodesple","",$TBmetodos[$idxc],100).'</TD>';
				}
				echo '<TR>'.chr(13);
			}
		}
	}	
	echo '<TR height=5><TD colspan='.$colums.' style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #FFFFFF;">&nbsp;</TD></TR>';
}
/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de los perfiles softwares
________________________________________________________________________________________________________*/
function HTMLSELECT_imagenes($cmd,$idimagen,$numpar,$codpar,$icp,$sw,$idordenadores,$ambito)
{
	$SelectHtml="";
	$cmd->texto="SELECT *,repositorios.ip as iprepositorio	FROM  imagenes
				INNER JOIN repositorios ON repositorios.idrepositorio=imagenes.idrepositorio"; 
	if($sw) // Imágenes con el mismo tipo de partición 
		$cmd->texto.=	"	WHERE imagenes.codpar=".$codpar;								
	else
		$cmd->texto.=	"	WHERE imagenes.codpar<>".$codpar;		
		
	$cmd->texto.=" AND imagenes.numpar>0 AND imagenes.codpar>0 AND imagenes.idrepositorio>0	"; // La imagene debe existir y estar creada	
    
	$idordenador1 = explode(",",$idordenadores);
	$idordenador=$idordenador1[0];
	if ($ambito == 16)
		$cmd->texto.=" AND repositorios.idrepositorio=(select idrepositorio from ordenadores where ordenadores.idordenador=" .$idordenador .") OR repositorios.ip=(select ip from ordenadores where ordenadores.idordenador=". $idordenador .")";
    else 
    	$cmd->texto.=" AND repositorios.idrepositorio=(select idrepositorio from ordenadores where ordenadores.idordenador=" .$idordenador .")";
    


	//echo $cmd->texto;

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if($sw) $des=1; else $des=0;
	$SelectHtml.= '<SELECT class="formulariodatos" id="despleimagen_'.$icp.'_'.$des.'" style="WIDTH:220">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';

	if ($rs->Abrir()){
		$rs->Primero(); 
		while (!$rs->EOF){
			$SelectHtml.='<OPTION value="'.$rs->campos["idimagen"]."_".$rs->campos["nombreca"]."_".$rs->campos["iprepositorio"]."_".$rs->campos["idperfilsoft"].'"';
			if($idimagen==$rs->campos["idimagen"]) $SelectHtml.=" selected ";
			$SelectHtml.='>';
			$SelectHtml.= $rs->campos["descripcion"].'</OPTION>';
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
	$SelectHtml.= '</SELECT>';
	return($SelectHtml);
}
/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de los repositorios
________________________________________________________________________________________________________*/
function HTMLSELECT_repositorios($cmd,$idcentro,$idrepositorio,$particion){
	$SelectHtml="";
	$rs=new Recordset; 
	
	$cmd->texto="SELECT nombrerepositorio,ip FROM  repositorios";
	$rs->Comando=&$cmd; 

	if (!$rs->Abrir()) return($SelectHtml); // Error al abrir recordset
	$SelectHtml.= '<SELECT class="formulariodatos" id="desplerepositorios_'.$particion.'" style="WIDTH: 200">';
	$rs->Primero(); 
	while (!$rs->EOF){
		$SelectHtml.='<OPTION value="'.$rs->campos["ip"].'"';
		if($rs->campos["idrepositorio"]==$idrepositorio) $SelectHtml.=" selected ";
		$SelectHtml.='>';
		$SelectHtml.= $rs->campos["nombrerepositorio"];
		$SelectHtml.='</OPTION>';
		$rs->Siguiente();
	}
	$SelectHtml.= '</SELECT>';
	$rs->Cerrar();
	return($SelectHtml);
}


function mcast_syntax($cmd,$ambito,$idambito)
{
//if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if ($ambito == 4) 
{
$cmd->texto='SELECT aulas.pormul,aulas.ipmul,aulas.modomul,aulas.velmul,aulas.modp2p,aulas.timep2p FROM  aulas where aulas.idaula=' . $idambito ;
}

if ($ambito == 8) 
{
$cmd->texto='SELECT aulas.pormul,aulas.ipmul,aulas.modomul,aulas.velmul,aulas.modp2p,aulas.timep2p FROM  aulas JOIN gruposordenadores ON aulas.idaula=gruposordenadores.idaula where gruposordenadores.idgrupo=' . $idambito ;
}

if ($ambito == 16)
{
$cmd->texto='SELECT aulas.pormul,aulas.ipmul,aulas.modomul,aulas.velmul,aulas.modp2p,aulas.timep2p FROM  aulas JOIN ordenadores ON ordenadores.idaula=aulas.idaula where ordenadores.idordenador=' . $idambito ;
}

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
if ($rs->Abrir()){
		$rs->Primero(); 
       	$mcastsyntax.= $rs->campos["pormul"] . ':';
        		
		$rs->Siguiente();
		switch ($rs->campos["modomul"]) 
		{
			case 1:
			    $mcastsyntax.="half-duplex:";
				break;
			default:
			    $mcastsyntax.="full-duplex:";
				break;
		} 			
		$rs->Siguiente();
		$mcastsyntax.=$rs->campos["ipmul"] . ':';
		
		$rs->Siguiente();
		$mcastsyntax.=$rs->campos["velmul"] .'M:';
		
	$rs->Cerrar();
	}
	     	$mcastsyntax.="50:";
			$mcastsyntax.="60";
	return($mcastsyntax);	
}














?>
