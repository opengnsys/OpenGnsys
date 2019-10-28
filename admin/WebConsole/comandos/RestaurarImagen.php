<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: RestaurarImagenOrdenador.php
// Descripción : 
//		Implementación del comando "RestaurarImagen" (Ordenadores)
// version 1.1: cliente con varios repositorios
//	HTMLSELECT_imagenes: Imagenes de todos los repositorios de la UO - Cambia parametro idordenadores por idambito
// autor: Irina Gomez, Universidad de Sevilla
// fecha 2015-06-17
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
include_once("../includes/opcionesprotocolos.php");
include_once("../idiomas/php/".$idioma."/comandos/restaurarimagen_".$idioma.".php");
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
<TITLE>Administración web de aulas</TITLE>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<STYLE TYPE="text/css"></STYLE>
<SCRIPT language="javascript" src="./jscripts/RestaurarImagen.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/arrays.js"></SCRIPT>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/restaurarimagen_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<?php
	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>';
	//________________________________________________________________________________________________________

	include_once("./includes/FiltradoAmbito.php");
	//________________________________________________________________________________________________________
				
	echo '<P align=center><SPAN align=center class=subcabeceras>'.$TbMsg[19].'</SPAN></P>';		
	if($ambito!=$AMBITO_ORDENADORES){	
		$cadenaid="";
		$cadenaip="";
		$cadenamac="";
		RecopilaIpesMacs($cmd,$ambito,$idambito);		
	?>
		<FORM action="RestaurarImagen.php" name="fdatos" method="POST">
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
	pintaConfiguraciones($cmd,$idambito,$ambito,9,$sws,false,"pintaParticionesRestaurarImagen","ipordenador");
	//________________________________________________________________________________________________________
	include_once("./includes/formularioacciones.php");
	//________________________________________________________________________________________________________
	//________________________________________________________________________________________________________
	include_once("./includes/opcionesacciones.php");
	//________________________________________________________________________________________________________
?>
<SCRIPT language="javascript">
	Sondeo();
</SCRIPT>
</BODY>
</HTML>
<?php

/*________________________________________________________________________________________________________
	Crea la etiqueta html <SELECT> de los perfiles softwares
// Version 0.1: En consulta SQL se quita imagenes.numpar>0. las imágenes recien creadas tienen numpar=0.
//      US ETSII - Irina Gomez - 2014-11-11
________________________________________________________________________________________________________*/
function HTMLSELECT_imagenes($cmd,$idimagen,$numpar,$codpar,$icp,$sw,$idambito,$ambito)
{
	global $IMAGENES_MONOLITICAS;

	$SelectHtml="";
	$cmd->texto="SELECT *,repositorios.ip as iprepositorio, repositorios.nombrerepositorio as nombrerepo FROM imagenes
				INNER JOIN repositorios ON repositorios.idrepositorio=imagenes.idrepositorio"; 
	if($sw) // Imágenes con el mismo tipo de partición 
		$cmd->texto.=	"	WHERE imagenes.codpar=".$codpar;
	else
		$cmd->texto.=	"	WHERE imagenes.codpar<>".$codpar;
		
	$cmd->texto.=" AND imagenes.idrepositorio>0";	// La imagene debe existir en el repositorio.
	$cmd->texto.=" AND imagenes.tipo=".$IMAGENES_MONOLITICAS;
    
	// 1.1 Imagenes de todos los repositorios de la UO.
	switch ($ambito) {
	    case 16:
		// ambito ordenador
		$selectrepo='select repositorios.idrepositorio from repositorios INNER JOIN aulas INNER JOIN ordenadores where repositorios.idcentro=aulas.idcentro AND aulas.idaula=ordenadores.idaula AND idordenador='.$idambito;
		break;	  
	    case 8:
		// ambito grupo ordenadores
		$selectrepo='select idrepositorio  from repositorios INNER JOIN aulas INNER JOIN gruposordenadores where repositorios.idcentro=aulas.idcentro AND aulas.idaula=gruposordenadores.idaula AND idgrupo='.$idambito;
		break;	  
	    case 4:
		// ambito aulas
		$selectrepo='select idrepositorio from repositorios INNER JOIN aulas where repositorios.idcentro=aulas.idcentro AND idaula='.$idambito;
		break;	  
	}
	$cmd->texto.=" AND repositorios.idrepositorio IN (".$selectrepo.") ORDER BY imagenes.descripcion";

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
			$SelectHtml.= $rs->campos["descripcion"].' ('.$rs->campos["nombrerepo"].') </OPTION>';

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



?>

