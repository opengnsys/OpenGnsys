<?php
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
include_once("../includes/RecopilaIpesMacs.php");
include_once("../idiomas/php/".$idioma."/avisos_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/configurar_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");
include_once("../includes/ConfiguracionesParticiones.php");

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
<HEAD>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<TITLE>Administración web de aulas</TITLE>
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<STYLE TYPE="text/css"></STYLE>
<SCRIPT language="javascript" src="./jscripts/Configurar.js"></SCRIPT>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/configurar_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<?php
	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>';
	//________________________________________________________________________________________________________
	//
	//include_once("./includes/FiltradoAmbito.php");

	//________________________________________________________________________________________________________
				
	echo '<P align=center><SPAN align=center class=subcabeceras>'.$TbMsg[19].'</SPAN></P>';		
	if($ambito!=$AMBITO_ORDENADORES){	
		$cadenaid="";
		$cadenaip="";
		$cadenamac="";
		RecopilaIpesMacs($cmd,$ambito,$idambito);	
		
	?>
		<FORM action="Configurar.php" name="fdatos" method="POST">
				<INPUT type="hidden" name="idambito" value="<?php echo $idambito?>">
				<INPUT type="hidden" name="ambito" value="<?php echo $ambito?>">			
				<INPUT type="hidden" name="cadenaid" value="<?php echo $cadenaid?>">			
				<INPUT type="hidden" name="nombreambito" value="<?php echo $nombreambito?>">
				<INPUT type="hidden" name="idcomando" value="<?php echo $idcomando?>">
				<INPUT type="hidden" name="descricomando" value="<?php echo $descricomando?>">
				<INPUT type="hidden" name="gestor" value="<?php echo $gestor?>">
				<INPUT type="hidden" name="funcion" value="<?php echo $funcion?>">
				<TABLE class="tabla_busquedas" align=center border=0 cellPadding=0 cellSpacing=0>
				<TR>
					<TH height=15 align="center" colspan=14><?php echo $TbMsg[18]?></TH>
				</TR>
				<TR>
					<TD align=right><?php echo $TbMsg[30]?></TD>
					<TD align=center><INPUT type="checkbox" value="<?php echo $msk_sysFi?>" name="fk_sysFi" <?php if($fk_sysFi==$msk_sysFi) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>

					<TD align=right><?php echo $TbMsg[32]?></TD>
					<TD align=center><INPUT type="checkbox" value="<?php echo $msk_tamano?>" name="fk_tamano" <?php if($fk_tamano==$msk_tamano) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>
				
					<TD align=right><?php echo $TbMsg[31]?></TD>
					<TD align=center><INPUT type="checkbox" value="<?php echo $msk_nombreSO?>" name="fk_nombreSO" <?php if($fk_nombreSO==$msk_nombreSO) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>				
				</TR>
				<TR>
					<TD height=2 style="BORDER-TOP:#999999 1px solid;" align="center" colspan=14>&nbsp;</TD>			
				</TR>
				<TR>
					<TD height=20 align="center" colspan=14>
						<A href=#>
						<IMG border=0 src="../images/boton_confirmar_<?php echo $idioma ?>.gif" onclick="document.fdatos.submit()"></A></TD>			
				</TR>
			</TABLE>
		</FORM>	
<?php
	}
	$sws=$fk_sysFi |  $fk_tamano | $fk_nombreSO;

	pintaConfiguraciones($cmd,$idambito,$ambito,7,$sws,false,"pintaParticionesConfigurar");	

	/* Dibuja tabla patron  !OJO! no insertar caracteres entre las etiquetas*/
	
	echo '<TABLE style="visibility:hidden"><TR id="TR_patron">';
	echo '<TD align=center><input id="CHK_patron" type="checkbox"></TD>';
	echo '<TD align=center>'.HTMLSELECT_particiones(0).'</TD>';
	echo '<TD align=center>'.HTMLSELECT_tipospar($cmd,"").'</TD>';
	echo '<TD align=center>'.HTMLSELECT_sistemasficheros($cmd,"").'</TD>';
	echo '<TD align=center><INPUT type="text" style="width:100px" value="0"></TD>';
	echo '<TD align=center>&nbsp;</TD>';
	echo '<TD align=center>'.opeFormatear().'</TD></TR></TABlE>';
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
<?php

/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de los número de particiones
________________________________________________________________________________________________________*/
function HTMLSELECT_particiones($p)
{
	global $TbMsg;
	
	$SelectHtml="";
	$opciones="";
	for($i=0;$i<9;$i++)
			$opciones.="$i=$i".chr(13);
	$opciones.="$i=$i";
	$SelectHtml.=HTMLCTESELECT($opciones,"particiones","estilodesple","",$p,40,"");
	return($SelectHtml);
}
/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de las operaciones
________________________________________________________________________________________________________*/
function opeFormatear()
{
	$ckhboxtHtml='<input type="checkbox" name=operaciones/>';
	return($ckhboxtHtml);
}
/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de los tipos de particiones
________________________________________________________________________________________________________*/
function HTMLSELECT_tipospar($cmd,$codpar)
{
	return(HTMLSELECT($cmd,0,"tipospar",$codpar,"tipopar","tipopar",150,"","formulariodatos","codpar<256"));
}	
/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de los sistemas de ficheros
________________________________________________________________________________________________________*/
function HTMLSELECT_sistemasficheros($cmd,$idsistemafichero)
{
	return(HTMLSELECT($cmd,0,"sistemasficheros",$idsistemafichero,"idsistemafichero","descripcion",150,"","formulariodatos"));
}	
?>

