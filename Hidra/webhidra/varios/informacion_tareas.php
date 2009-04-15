<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 200-2005 José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: informacion_tareas.php
// Descripción : 
//		Muestra los comandos que forman parte de una tarea  y sus valores
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../idiomas/php/".$idioma."/informacion_tareas_".$idioma.".php");
//________________________________________________________________________________________________________
$idtarea=""; 
$descripcioncomando=""; 

if (isset($_GET["idtarea"]))	$idtarea=$_GET["idtarea"]; 
if (isset($_GET["descripciontarea"]))	$descripciontarea=$_GET["descripciontarea"]; 
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../hidra.css">
</HEAD>
<BODY>
	<?
	$cmd=CreaComando($cadenaconexion);
	if ($cmd){
		$rs=new Recordset; 
		$cmd->texto="SELECT idtareacomando FROM tareas_comandos  WHERE idtarea=".$idtarea. " ORDER BY orden";
		$rs->Comando=&$cmd; 
		if ($rs->Abrir()){
			echo '<br><p align=center><IMG src="../images/iconos/tareas.gif">&nbsp;&nbsp;<U><span class=cabeceras>'.$TbMsg[0].'</span></U><br><span class=subcabeceras>'.$descripciontarea.'</span></p>';
			$tabla_parametros=""; // Tabla  para localizar parametros
			$cont_parametros=0; // Contador de la tabla 
			CreaTablaParametros($cmd); // Crea tabla  especificaciones de parametros
			while (!$rs->EOF){
				pintacomandos($cmd,$rs->campos["idtareacomando"]);
				$rs->Siguiente();
			}
		}
	}
	?>
</BODY>
</HTML>	
<?
// *************************************************************************************************************************************************
function pintacomandos($cmd,$idtareacomando){
	global $TbMsg;
	global $AMBITO_CENTROS;
	global $AMBITO_GRUPOSAULAS;
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;
	global  $tabla_parametros;
	global  $cont_parametros;

	$rs=new Recordset; 
	$cmd->texto="SELECT tareas_comandos.*, comandos.visuparametros FROM tareas_comandos ";
	$cmd->texto.=" INNER JOIN comandos ON comandos.idcomando=tareas_comandos.idcomando";
	$cmd->texto.=" WHERE tareas_comandos.idtareacomando=".$idtareacomando;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return($cadenaXML); // Error al abrir recordset
	if ($rs->EOF) return("");

	$HTMLparametros='<TABLE class="tabla_parametros" align=center  border=0 cellspacing=1 cellpadding=0 width="90%">'.chr(13);
	$HTMLparametros.='<TR>'.chr(13);
	$HTMLparametros.=  '<TH>&nbsp;'.$TbMsg[1].'&nbsp;</TH>'.chr(13);
	$HTMLparametros.=  '<TH>&nbsp;'.$TbMsg[2].'</TH>&nbsp;'.chr(13);
	$HTMLparametros.=  '</TR>'.chr(13);

	$textambito="";
	$urlimg="";

	switch($rs->campos["ambito"]){
		case $AMBITO_CENTROS :
			$urlimg='../images/iconos/centros.gif';
			$textambito="Center";
			$nombre=TomaDato($cmd,0,'centros',$rs->campos["idambito"],'idcentro','nombrecentro');
			break;
		case $AMBITO_GRUPOSAULAS :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito="Group of labs";
			$nombre=TomaDato($cmd,0,'grupos',$rs->campos["idambito"],'idgrupo','nombregrupo');
			break;
		case $AMBITO_AULAS :
			$urlimg='../images/iconos/aula.gif';
			$textambito="Labs";
			$nombre=TomaDato($cmd,0,'aulas',$rs->campos["idambito"],'idaula','nombreaula');
			break;
		case $AMBITO_GRUPOSORDENADORES :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito="Group of computers";
			$nombre=TomaDato($cmd,0,'gruposordenadores',$rs->campos["idambito"],'idgrupo','nombregrupoordenador');
			break;
		case $AMBITO_ORDENADORES :
			$urlimg='../images/iconos/ordenador.gif';
			$textambito="Computers";
			$nombre=TomaDato($cmd,0,'ordenadores',$rs->campos["idambito"],'idordenador','nombreordenador');
			break;
	}
	$HTMLparametros.= '<TD>&nbsp;'.$TbMsg[3].'&nbsp;</TD>'.chr(13);
	$HTMLparametros.= '<TD>&nbsp;'.$textambito.'&nbsp;';
	$HTMLparametros.= '<IMG src="'.$urlimg.'">&nbsp;</TD>'.chr(13);
	$HTMLparametros.=	'</TR><TR>';
	$HTMLparametros.= '<TD>&nbsp;'.$TbMsg[4].'&nbsp;</TD>'.chr(13);
	$HTMLparametros.= '<TD>&nbsp;'.$nombre.'&nbsp;</TD>'.chr(13);
	$HTMLparametros.=	'</TR>';

	$auxVP=split(";",$rs->campos["visuparametros"]); // Parametros visualizables
	$auxP=split(chr(13),$rs->campos["parametros"]); // Recorre parametros para visualizar los que así sean
	for ($i=0;$i<sizeof($auxP);$i++){
		$dualparam=split("=",$auxP[$i]);
		for ($k=0;$k<sizeof($auxVP);$k++){
			 if($auxVP[$k]==$dualparam[0]){
				$posp=busca_indicebinariodual($dualparam[0],$tabla_parametros,$cont_parametros); // Busca datos del parámetro en la tabla cargada previamentre con todos los parámetros
				if ($posp>=0){
					$auxtabla_parametros=$tabla_parametros[$posp][1];
					$HTMLparametros.='<TR>'.chr(13);
					$HTMLparametros.=  '<TD>&nbsp;'.$auxtabla_parametros["descripcion"].'&nbsp;</TD>'.chr(13);
					if($auxtabla_parametros["tipopa"]==1){
					$valor=TomaDato($cmd,0,$auxtabla_parametros["nomtabla"],$dualparam[1],$auxtabla_parametros["nomidentificador"],$auxtabla_parametros["nomliteral"]);
					}else
						$valor=$dualparam[1];
					if($dualparam[0]!="iph") 
							$HTMLparametros.=  '<TD>&nbsp;'.Urldecode($valor).'&nbsp;</TD>'.chr(13);
					else{
							$tablaipes=PintaOrdenadores($cmd,$valor);
							$HTMLparametros.=  '<TD>&nbsp;'.$tablaipes.'&nbsp;</TD>'.chr(13);
					}
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
//________________________________________________________________________________________________________
function PintaOrdenadores($cmd,$cadenaip){
	$auxP=split(";",$cadenaip); 
	if(sizeof($auxP)<1) return("");
	$clauslaIN="'".$auxP[0]."'";
	for ($i=1;$i<sizeof($auxP);$i++)
		$clauslaIN.=",'".$auxP[$i]."'";
	$rs=new Recordset; 
	$contor=0;
	$maxord=7; // Máximos ordenadores por linea
	$cmd->texto=" SELECT nombreordenador,ip FROM ordenadores  INNER JOIN aulas ON aulas.idaula=ordenadores.idaula WHERE ip IN(".$clauslaIN.") ORDER by nombreaula,nombreordenador";
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(""); // Error al abrir recordset
	$tablaHtml='<TABLE align=left border=0><TR>';
	while (!$rs->EOF){
		$contor++;
		$tablaHtml.= '<TD align=center style="BACKGROUND-COLOR: #b5daad;FONT-FAMILY: Arial, Helvetica, sans-serif;	BORDER-BOTTOM:#000000 none;FONT-SIZE: 8px"><IMG src="../images/iconos/ordenador.gif"><br><span style="FONT-SIZE:9px" >'.$rs->campos["nombreordenador"].'</TD>';
		if($contor>$maxord){
			$contor=0;
			$tablaHtml.='</TR><TR>';
		}
		$rs->Siguiente();
	}
	$tablaHtml.='</TR>';
	$tablaHtml.= '</TR></TABLE>';
	return($tablaHtml);
}
?>