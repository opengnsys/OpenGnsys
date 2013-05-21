<?
// ********************************************************************************************************
// Aplicaci�n WEB: ogAdmWebCon
// Autor: Jos� Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creaci�n: A�o 2012
// Fecha �ltima modificaci�n: Noviembre-2012
// Nombre del fichero: RestaurarSoftIncremental.php
// Descripci�n : 
//		Implementaci�n del comando "RestaurarSoftIncremental"
// ********************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../includes/HTMLCTESELECT.php");
include_once("../includes/TomaDato.php");
include_once("../includes/RecopilaIpesMacs.php");
include_once("../idiomas/php/".$idioma."/comandos/restaurarsoftincremental_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");
include_once("./includes/capturaacciones.php");
include_once("../includes/ConfiguracionesParticiones.php");

//________________________________________________________________________________________________________
//
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexi�n con servidor B.D.
//________________________________________________________________________________________________________
//
//
// Captura par�metros
//________________________________________________________________________________________________________
//

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
//
?>
<HTML>
<TITLE>Administraci�n web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<STYLE TYPE="text/css"></STYLE>
<SCRIPT language="javascript" src="./jscripts/RestaurarSoftIncremental.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/restaurarsoftincremental_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<?
//________________________________________________________________________________________________________
//
//
//	Cabecera 
//________________________________________________________________________________________________________
//	
//
	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>'; // T�tulo
	include_once("./includes/FiltradoAmbito.php");
//________________________________________________________________________________________________________
//
	echo '<P align=center><SPAN align=center class=subcabeceras>'.$TbMsg[19].'</SPAN></P>';		
	if($ambito!=$AMBITO_ORDENADORES){	
		$cadenaid="";
		$cadenaip="";
		$cadenamac="";
		RecopilaIpesMacs($cmd,$ambito,$idambito);		
	?>
		<FORM action="RestaurarSoftIncremental.php" name="fdatos" method="POST">
				<INPUT type="hidden" name="idambito" value="<? echo $idambito?>">
				<INPUT type="hidden" name="ambito" value="<? echo $ambito?>">	
				<INPUT type="hidden" name="cadenaid" value="<? echo $cadenaid?>">				
				<TABLE class="tabla_busquedas" align=center border=0 cellPadding=0 cellSpacing=0>
				<TR>
					<TH height=15 align="center" colspan=14><? echo $TbMsg[18]?></TH>
				</TR>
				<TR>
					<TD align=right><? echo $TbMsg[30]?></TD>
					<TD align=center><INPUT onclick="document.fdatos.submit()" type="checkbox" value="<? echo $msk_sysFi?>" name="fk_sysFi" <? if($fk_sysFi==$msk_sysFi) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>

					<TD align=right><? echo $TbMsg[32]?></TD>
					<TD align=center><INPUT onclick="document.fdatos.submit()" type="checkbox" value="<? echo $msk_tamano?>" name="fk_tamano" <? if($fk_tamano==$msk_tamano) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>
				
					<TD align=right><? echo $TbMsg[31]?></TD>
					<TD align=center><INPUT onclick="document.fdatos.submit()" type="checkbox" value="<? echo $msk_nombreSO?>" name="fk_nombreSO" <? if($fk_nombreSO==$msk_nombreSO) echo " checked "?>></TD>
					<TD width="20" align=center>&nbsp;</TD>				
				</TR>
				<TR>
					<TD height=2 style="BORDER-TOP:#999999 1px solid;" align="center" colspan=14>&nbsp;</TD>			
				</TR>
			</TABLE>
		</FORM>	
<?
	}
	$sws=$fk_sysFi |  $fk_tamano | $fk_nombreSO;
	pintaConfiguraciones($cmd,$idambito,$ambito,9,$sws,true,"pintaParticionesRestaurarImagenSincronizacion1");	
	echo "<br>";
	opcionesAdicionales();
//________________________________________________________________________________________________________
//
	include_once("./includes/formularioacciones.php");
	include_once("./includes/opcionesacciones.php");
//________________________________________________________________________________________________________
//
?>
<SCRIPT language="javascript">
	Sondeo();
</SCRIPT>
</BODY>
</HTML>
<?

/*________________________________________________________________________________________________________

	Crea la etiqueta html <SELECT> de las im�genes
________________________________________________________________________________________________________*/
function HTMLSELECT_imagenes($cmd,$idimagen,$numpar,$codpar,$icp,$sw,$idordenadores,$ambito)
{
	global $IMAGENES_INCREMENTALES;
	global $AMBITO_ORDENADORES;

	$SelectHtml="";
	$cmd->texto="SELECT imagesbas.*,repositorios.ip as iprepositorio,repositorios.nombrerepositorio,
				imagenes.idperfilsoft as idperfilsoftinc,imagenes.idimagen as idimageninc,
				imagenes.nombreca as nombrecainc,imagenes.descripcion as descripcioninc
				FROM  imagenes
				INNER JOIN imagenes as imagesbas on imagesbas.idimagen = imagenes.imagenid
				INNER JOIN repositorios ON repositorios.idrepositorio=imagesbas.idrepositorio"; 
	if($sw) // Im�genes con el mismo tipo de partici�n 
		$cmd->texto.=	"	WHERE imagesbas.codpar=".$codpar;								
	else
		$cmd->texto.=	"	WHERE imagesbas.codpar<>".$codpar;		
		
	$cmd->texto.=" AND imagenes.tipo=".$IMAGENES_INCREMENTALES;
//					AND imagenes.idperfilsoft>=0"; // La imagene debe existir y estar creada	
    
	$idordenador1 = explode(",",$idordenadores);
	$idordenador=$idordenador1[0];
	if ($ambito == $AMBITO_ORDENADORES)
		$cmd->texto.=" AND (repositorios.idrepositorio=(select idrepositorio from ordenadores where ordenadores.idordenador=" .$idordenador .") 
							OR repositorios.ip=(select ip from ordenadores where ordenadores.idordenador=". $idordenador ."))";
    else 
    	$cmd->texto.=" AND repositorios.idrepositorio=(select idrepositorio from ordenadores where ordenadores.idordenador=" .$idordenador .")";
    
	//echo $cmd->texto;

	$rs=new Recordset; 
	$rs->Comando=&$cmd; 
	if($sw) $des=1; else $des=0;
	$SelectHtml.= '<SELECT class="formulariodatos" id="despleimagen_'.$icp.'_'.$des.'" style="width:95%">';
	$SelectHtml.= '    <OPTION value="0"></OPTION>';

	if ($rs->Abrir()){
		$rs->Primero(); 
		while (!$rs->EOF){
			$SelectHtml.='<OPTION 
			value="'.$rs->campos["idimagen"]."_".$rs->campos["nombreca"]."_".$rs->campos["iprepositorio"]."_".$rs->campos["idperfilsoftinc"]."_".$rs->campos["idimageninc"]."_".$rs->campos["nombrecainc"]."_".$rs->campos["ruta"].'"';
			if($idimagen==$rs->campos["idimagen"]) $SelectHtml.=" selected ";
			$SelectHtml.='>';
			$SelectHtml.= $rs->campos["descripcioninc"].'</OPTION>';
			$rs->Siguiente();
		}
		$rs->Cerrar();
	}
	$SelectHtml.= '</SELECT>';
	return($SelectHtml);
}
/*----------------------------------------------------------------------------------------------
	Dibuja una tabla con las opciones generales
----------------------------------------------------------------------------------------------*/
function opcionesAdicionales()
{
	global $TbMsg;	
	
	$tablaHtml='<table width="95%" align=center border=0 cellPadding=0 cellSpacing=0 class="tabla_accesos">';
	$tablaHtml.='<tr><th colspan=8 align=center><b>&nbsp;'.$TbMsg[11].'&nbsp;</b></th></tr>';
	$tablaHtml.='<tr id="trOpc">
					<td align=right>'.$TbMsg[35].'</td>
					<td><input  type=checkbox name="bpi"></td>'; // Borrar imagen previamente del servidor 			
	$tablaHtml.='	
					<td  align=right>'.$TbMsg[36].'</td>
					<td><input type=checkbox name="cpc"></td>'; // Copiar adem�s la imagen a la cach�
	$tablaHtml.='		
					<td  align=right>'.$TbMsg[37].'</td>
					<td><input type=checkbox name="bpc"></td>'; // Borrar imagen de la cach� previamente antes de copiarla 		
	$tablaHtml.='		
					<td  align=right>'.$TbMsg[39].'</td>
					<td><input type=checkbox name="nba"></td>'; // No borrar archivos en destino  						
	$tablaHtml.='</tr>';
	$tablaHtml.='	</table>';
	echo $tablaHtml;
}
?>

