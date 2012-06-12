<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
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
include_once("../includes/TomaDato.php");
include_once("../includes/ConfiguracionesParticiones.php");
include_once("../includes/RecopilaIpesMacs.php");
include_once("../idiomas/php/".$idioma."/comandos/configurar_".$idioma.".php");
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
<SCRIPT language="javascript" src="./jscripts/Configurar.js"></SCRIPT>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/configurar_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<?
	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>';
	//________________________________________________________________________________________________________
	//
		include_once("./includes/FiltradoAmbito.php");
	//________________________________________________________________________________________________________
				
	echo '<P align=center><SPAN align=center class=subcabeceras>'.$TbMsg[19].'</SPAN></P>';		
	if($ambito!=$AMBITO_ORDENADORES){	
		$cadenaid="";
		$cadenaip="";
		$cadenamac="";
		RecopilaIpesMacs($cmd,$ambito,$idambito);	
		
	?>
		<FORM action="Configurar.php" name="fdatos" method="POST">
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
						<IMG border=0 src="../images/boton_confirmar_<? echo $idioma ?>.gif" onclick="document.fdatos.submit()"></A></TD>			
				</TR>
			</TABLE>
		</FORM>	
<?
	}
	$sws=$fk_sysFi |  $fk_tamano | $fk_nombreSO;
	pintaConfiguraciones($cmd,$idambito,$ambito,7,$sws,true);	
	
	/* Dibuja tabla patron  !OJO! no insertar caracteres entre las etiquetas*/
	
	echo '<TABLE style="visibility:hidden"><TR id="TR_patron">';
	echo '<TD align=center><input id="CHK_patron" type="checkbox"></TD>';
	echo '<TD align=center>'.HTMLSELECT_particiones(0).'</TD>';
	echo '<TD align=center>'.HTMLSELECT_tipospar($cmd,0).'</TD>';
	echo '<TD align=center>'.HTMLSELECT_sistemasficheros($cmd,0).'</TD>';
	echo '<TD align=center><INPUT type="text" style="width:100" value="0"></TD>';
	echo '<TD align=center>&nbsp;</TD>';
	echo '<TD align=center>'.HTMLSELECT_operaciones().'</TD></TR></TABlE>';
	//________________________________________________________________________________________________________
	include_once("./includes/formularioacciones.php");
	//________________________________________________________________________________________________________
	$swb=true; // Este switch hace que se muestren o se oculten los botonotes de confirmación
	//________________________________________________________________________________________________________
	include_once("./includes/opcionesacciones.php");
	//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
/*________________________________________________________________________________________________________

	Descripción:
		(Esta función es llamada por pintaConfiguraciones que está incluida en ConfiguracionesParticiones.php)
		Crea una taba html con las especificaciones de particiones de un ambito ya sea ordenador,
		grupo de ordenadores o aula
	Parametros:
		$configuraciones: Cadena con las configuraciones de particioners del ámbito. El formato 
						sería una secuencia de cadenas del tipo "clave de configuración" separados por "@" 
						Ejemplo:1;7;30000000;3;3;0;@2;130;20000000;5;4;0;@3;131;1000000;0;0;0;0
		$idordenadores: cadena con los identificadores de los ordenadores que forman parte del bloque 
		$cc: Identificador de la configuración
	Devuelve:
		El código html de la tabla
________________________________________________________________________________________________________*/
function pintaParticiones($cmd,$configuraciones,$idordenadores,$cc)
{
	global $tbKeys; // Tabla contenedora de claves de configuración
	global $conKeys; // Contador de claves de configuración
	global $TbMsg;

	$colums=7;
	echo '<TR id="TR_'.$cc.'">';
	echo '<TH align=center><IMG src="../images/iconos/eliminar.gif"></TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[8].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[24].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[27].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[22].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[21].'&nbsp;</TH>';
	echo '<TH align=center>&nbsp;'.$TbMsg[12].'&nbsp;</TH>';	
	echo '</TR>';

	$auxCfg=split("@",$configuraciones); // Crea lista de particiones
	for($i=0;$i<sizeof($auxCfg);$i++){
		$auxKey=split(";",$auxCfg[$i]); // Toma clave de configuracion
		for($k=0;$k<$conKeys;$k++){ // Busca los literales para las claves de esa partición
			if($tbKeys[$k]["cfg"]==$auxCfg[$i]){ // Claves encontradas
				$icp=$cc."_".$k; // Identificador de la configuración-partición
				echo '<TR id="TR_'.$icp.'">';
				echo '<TD align=center><input type=checkbox onclick="eliminaParticion(\''.$icp.'\',this)"></TD>';
			
				echo '<TD align=center>'.HTMLSELECT_particiones($tbKeys[$k]["numpar"]).'</TD>';
				echo '<TD align=center>'.HTMLSELECT_tipospar($cmd,$tbKeys[$k]["codpar"]).'</TD>';
				
				$sf=tomaSistemasFicheros($tbKeys[$k]["numpar"],$idordenadores,true);	
				echo'<TD align=center>'.HTMLSELECT_sistemasficheros($cmd,$sf).'</TD>';

				$tm=tomaTamano($tbKeys[$k]["numpar"],$idordenadores);
				echo'<TD align=center><INPUT type="text" style="width:100" value="'.$tm.'"></TD>';		
					
				echo '<TD align=center>'.tomaNombresSO($tbKeys[$k]["numpar"],$idordenadores).'</TD>';					
			
				echo '<TD align=center>'.HTMLSELECT_operaciones().'</TD>';
				echo '</TR>';
			}
		}
	}
	/* Botones de añadir y confirmar */	
	echo '<TR id="TRIMG_'.$cc.'" height=5><TD colspan='.$colums.' style="BORDER-TOP: #999999 1px solid;BACKGROUND-COLOR: #FFFFFF;">&nbsp;</TD></TR>';
	echo '<TR height=30><TD style="BACKGROUND-COLOR: #FFFFFF;" colspan='.$colums.' align=center>';
	echo '	<A href="#add" style="text-decoration:none">
						<IMG id="IMG_'.$icp.'" border=0 src="../images/boton_insertar.gif" 
						value="'.$k.'" onclick="addParticion(this,'.$cc.')"></A>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<A href="#add" style="text-decoration:none">
						<IMG border=0 src="../images/boton_aceptar.gif" onclick="Confirmar('.$cc.')"></A></TD>
					</TR>';
}
/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de los número de particiones
________________________________________________________________________________________________________*/
function HTMLSELECT_particiones($p)
{
	global $TbMsg;
	
	$SelectHtml="";
	$opciones="";
	for($i=0;$i<8;$i++)
			$opciones.="$i=$i".chr(13);
	$opciones.="$i=$i";
	$SelectHtml.=HTMLCTESELECT($opciones,"particiones","estilodesple","",$p,40,"");
	return($SelectHtml);
}
/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de las operaciones
________________________________________________________________________________________________________*/
function HTMLSELECT_operaciones()
{
	global $TbMsg;
	
	$SelectHtml="";
	$opciones="1=".$TbMsg[14]."".chr(13);
	$opciones.="2=".$TbMsg[15]."".chr(13);
	$opciones.="3=".$TbMsg[16]."";
	$SelectHtml.=HTMLCTESELECT($opciones,"operaciones","estilodesple",$TbMsg[13],0,100,"");
	return($SelectHtml);
}
/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de los tipos de particiones
________________________________________________________________________________________________________*/
function HTMLSELECT_tipospar($cmd,$codpar)
{
	return(HTMLSELECT($cmd,0,"tipospar",$codpar,"codpar","tipopar",150,"","formulariodatos"));
}	
/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de los sistemas de ficheros
________________________________________________________________________________________________________*/
function HTMLSELECT_sistemasficheros($cmd,$idsistemafichero)
{
	return(HTMLSELECT($cmd,0,"sistemasficheros",$idsistemafichero,"idsistemafichero","descripcion",150,"","formulariodatos"));
}	
?>
