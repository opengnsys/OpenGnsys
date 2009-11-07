<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: informacion_procedimientos.php
// Descripción : 
//		Muestra los comandos que forman parte de un procedimiento y sus valores
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../idiomas/php/".$idioma."/informacion_procedimientos_".$idioma.".php");
//________________________________________________________________________________________________________
$idprocedimiento=""; 
$descripcioncomando=""; 

if (isset($_GET["idprocedimiento"]))	$idprocedimiento=$_GET["idprocedimiento"]; 
if (isset($_GET["descripcionprocedimiento"]))	$descripcionprocedimiento=$_GET["descripcionprocedimiento"]; 
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html;charset=ISO-8859-1"> 
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
</HEAD>
<BODY>
	<?
	$cmd=CreaComando($cadenaconexion);
	if ($cmd){
		$rs=new Recordset; 
		$cmd->texto="SELECT idprocedimientocomando FROM procedimientos_comandos  WHERE idprocedimiento=".$idprocedimiento. " ORDER BY idprocedimientocomando,orden";
		$rs->Comando=&$cmd; 
		if ($rs->Abrir()){
			echo '<br><p align=center><IMG src="../images/iconos/procedimiento.gif">&nbsp;&nbsp;<U><span class=cabeceras>'.$TbMsg[0].'</span></U><br><span class=subcabeceras>'.$descripcionprocedimiento.'</span></p>';
			while (!$rs->EOF){
				$tabla_parametros=""; // Tabla  para localizar parametros
				$cont_parametros=0; // Contador de la tabla 
				CreaTablaParametros($cmd,&$tabla_parametros,&$cont_parametros); // Crea tabla  especificaciones de parametros
				pintacomandos($cmd,$rs->campos["idprocedimientocomando"]);
				$rs->Siguiente();
			}
		}
	}
	?>
</BODY>
</HTML>	
<?
// *************************************************************************************************************************************************
function pintacomandos($cmd,$idprocedimientocomando){
	global $TbMsg;
	global $AMBITO_CENTROS;
	global $AMBITO_GRUPOSAULAS;
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;
	global  $tabla_parametros;
	global  $cont_parametros;

	$rs=new Recordset; 
	$cmd->texto="SELECT procedimientos_comandos.*, comandos.visuparametros FROM procedimientos_comandos ";
	$cmd->texto.=" INNER JOIN comandos ON comandos.idcomando=procedimientos_comandos.idcomando";
	$cmd->texto.=" WHERE procedimientos_comandos.idprocedimientocomando=".$idprocedimientocomando;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	if ($rs->EOF) return("");

	$HTMLparametros='<TABLE class="tabla_parametros" align=center  border=0 cellspacing=1 cellpadding=0 width="90%" >'.chr(13);
	$HTMLparametros.='<TR>'.chr(13);
	$HTMLparametros.=  '<TH >&nbsp;'.$TbMsg[1].'&nbsp;</TH>'.chr(13);
	$HTMLparametros.=  '<TH>&nbsp;'.$TbMsg[2].'</TH>&nbsp;'.chr(13);
	$HTMLparametros.=  '</TR>'.chr(13);

	$textambito="";
	$urlimg="";
	$auxVP=split(";",$rs->campos["visuparametros"]); // Parametros visualizables
	$auxP=split(chr(13),$rs->campos["parametros"]); // Recorre parametros para visualizar los que as�sean
	for ($i=0;$i<sizeof($auxP);$i++){
		$dualparam=split("=",$auxP[$i]);
		for ($k=0;$k<sizeof($auxVP);$k++){
			 if($auxVP[$k]==$dualparam[0]){
				$posp=busca_indicebinariodual($dualparam[0],$tabla_parametros,$cont_parametros); // Busca datos del par�etro en la tabla cargada previamentre con todos los par�etros
				if ($posp>=0){
					$auxtabla_parametros=$tabla_parametros[$posp][1];
					$HTMLparametros.='<TR>'.chr(13);
					$HTMLparametros.=  '<TD >&nbsp;'.$auxtabla_parametros["descripcion"].'&nbsp;</TD>'.chr(13);
					if($auxtabla_parametros["tipopa"]==1){	$valor=TomaDato($cmd,0,$auxtabla_parametros["nomtabla"],$dualparam[1],$auxtabla_parametros["nomidentificador"],$auxtabla_parametros["nomliteral"]);
					}else
					$valor=$dualparam[1];
					$HTMLparametros.=  '<TD>&nbsp;'.Urldecode($valor).'&nbsp;</TD>'.chr(13);
					$HTMLparametros.=  '</TR>'.chr(13);
				}
			}
		}
	}
	$HTMLparametros.=  '</TABLE>'.chr(13);
	$descripcioncomando=TomaDato($cmd,0,"comandos",$rs->campos["idcomando"],"idcomando","descripcion");
	echo '<br><p align=center><IMG src="../images/iconos/comandos.gif">&nbsp;&nbsp;<span class=presentaciones>'.$descripcioncomando.'</span>';
	echo $HTMLparametros;
	echo '</p>';
}
?>