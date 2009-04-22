<?
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 200-2005 José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Febrero-2005
// Nombre del fichero: procedimientoscomandos.php
// Descripción : 
//		Administra los comandos que forman parte de un procedimiento
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../idiomas/php/".$idioma."/procedimientoscomandos_".$idioma.".php");
//________________________________________________________________________________________________________
$idprocedimiento=0; 
$descripcionprocedimiento=""; 
if (isset($_GET["idprocedimiento"])) $idprocedimiento=$_GET["idprocedimiento"]; // Recoge parametros
if (isset($_GET["descripcionprocedimiento"])) $descripcionprocedimiento=$_GET["descripcionprocedimiento"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
<LINK rel="stylesheet" type="text/css" href="../hidra.css">
<SCRIPT language="javascript" src="../jscripts/procedimientoscomandos.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/procedimientoscomandos_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<INPUT type=hidden value="<? echo $idcentro?>" id=idcentro>	 
	<P align=center class=cabeceras><IMG src="../images/iconos/procedimiento.gif">&nbsp;<?echo $TbMsg[0]?><BR>
	<SPAN align=center class=subcabeceras><?echo $TbMsg[1]?></span>&nbsp;<img src="../images/iconos/comandos.gif"><BR><BR>
	<SPAN align=center class=presentaciones><B><?echo $TbMsg[2]?>:&nbsp;</B><? echo $descripcionprocedimiento?></SPAN></P>
	<TABLE  align=center class="tabla_listados" cellspacing=1 cellpadding=0  id="TABLACOMANDOS">
				<TR  height=20>
					<TH >&nbsp;</TH>
					<TH ><IMG src="../images/iconos/eliminar.gif"></TH>
					<TH  colspan=2 align=left>&nbsp;<?echo $TbMsg[3]?></TH>
					<TH  ><?echo $TbMsg[4]?></TH>
					<TH  >A</TH>
			</TR>
		<?
			$rs=new Recordset; 
			$cmd->texto='SELECT     procedimientos_comandos.*, comandos.descripcion,comandos.visuparametros FROM procedimientos_comandos INNER JOIN comandos ON procedimientos_comandos.idcomando = comandos.idcomando WHERE procedimientos_comandos.idprocedimiento='.$idprocedimiento.' ORDER BY procedimientos_comandos.orden';
			$rs->Comando=&$cmd; 
			if ($rs->Abrir()){ 
				$rs->Primero();
				$tabla_parametros=""; // Tabla  para localizar parametros
				$cont_parametros=0; // Contador de la tabla 
				CreaTablaParametros($cmd); // Crea tabla  especificaciones de parametros
				while (!$rs->EOF){
						 echo '<TR height=20  id=TR-'.$rs->campos["idprocedimientocomando"].'>';
						echo '<TD   align=center><IMG onclick="ver_parametros(this,0,'.$rs->campos["idprocedimientocomando"].');" style="cursor:hand;display:block" src="../images/tsignos/contra.gif"><IMG onclick="ver_parametros(this,1,'.$rs->campos["idprocedimientocomando"].');"style="cursor:hand;display:none" src="../images/tsignos/desple.gif"></TD>';
						 echo '<TD  align=center ><INPUT  id=checkbox-'.$rs->campos["idprocedimientocomando"].' type=checkbox						 onclick="gestion_comandos('.$rs->campos["idprocedimientocomando"].',this)" checked ></INPUT></TD>';
						// Descripcion de la comando
						 echo '<TD  colspan=2>&nbsp;'.$rs->campos["descripcion"].'</TD>';
						// Orden del item del item
						echo '<TD  valign="meddle"  align=center >&nbsp;<INPUT class="formulariodatos" id=orden-'.$rs->campos["idprocedimientocomando"].' style="WIDTH:20px" type=text value="'.$rs->campos["orden"].'"></INPUT></TD>';
						echo '<TD   align=center id="imgact-'.$rs->campos["idprocedimientocomando"].'"><IMG src="../images/iconos/actualizar.gif" style="cursor:hand" onclick="ActualizarAccion('.$rs->campos["idprocedimientocomando"].')"></TD>';
						echo '</TR>';
						pintacomandos($cmd,$rs);
						$rs->Siguiente();
				}
			}
			$rs->Cerrar();
		?>
		</TABLE>
		<br>

		<P align=center><span align=center class=notas><I><?echo $TbMsg[7]?>.</I></P>
</FORM>
<?
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
// *************************************************************************************************************************************************
function pintacomandos($cmd,$rs){
	global $TbMsg;
	global $AMBITO_CENTROS;
	global $AMBITO_GRUPOSAULAS;
	global $AMBITO_AULAS;
	global $AMBITO_GRUPOSORDENADORES;
	global $AMBITO_ORDENADORES;
	global  $tabla_parametros;
	global  $cont_parametros;

	$HTMLparametros='<TR  id="PAR-'.$rs->campos["idprocedimientocomando"].'" >'.chr(13);
	$HTMLparametros.= '<TD>&nbsp;</TD>'.chr(13);
	$HTMLparametros.= '<TD>&nbsp;</TD>'.chr(13);
	$HTMLparametros.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4; " >'.$TbMsg[5].'</TH>'.chr(13);
	$HTMLparametros.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4;" >'.$TbMsg[6].'</TH>'.chr(13);
	$HTMLparametros.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4;" >&nbsp;</TH>'.chr(13);
	$HTMLparametros.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4;" >&nbsp;</TH>'.chr(13);
	$HTMLparametros.=  '</TR>'.chr(13);

	$urlimg="";
	$auxVP=split(";",$rs->campos["visuparametros"]); // Parametros visualizables
	$auxP=split(chr(13),$rs->campos["parametros"]); // Recorre parametros para visualizar los que así sean
	for ($i=0;$i<sizeof($auxP);$i++){
		$dualparam=split("=",$auxP[$i]);
		for ($k=0;$k<sizeof($auxVP);$k++){
			 if($auxVP[$k]==$dualparam[0]){
				$posp=busca_indicebinariodual($dualparam[0],$tabla_parametros,$cont_parametros); // Busca datos del parámetro en la tabla cargada previamentre con todos los parámetros
				if ($posp>=0){
					$auxtabla_parametros=$tabla_parametros[$posp][1];
					$HTMLparametros.='<TR  id="PAR-'.$rs->campos["idprocedimientocomando"].'" >'.chr(13);
					$HTMLparametros.= '<TD>&nbsp;</TD>'.chr(13);
					$HTMLparametros.= '<TD>&nbsp;</TD>'.chr(13);
					$HTMLparametros.=  '<TD style="BACKGROUND-COLOR: #b5daad;">&nbsp;'.$auxtabla_parametros["descripcion"].'&nbsp;</TD>'.chr(13);
					if($auxtabla_parametros["tipopa"]==1){
					$valor=TomaDato($cmd,0,$auxtabla_parametros["nomtabla"],$dualparam[1],$auxtabla_parametros["nomidentificador"],$auxtabla_parametros["nomliteral"]);
					}else
						$valor=$dualparam[1];
					if($dualparam[0]!="iph") 
							$HTMLparametros.=  '<TD style="BACKGROUND-COLOR: #b5daad;">&nbsp;'.$valor.'&nbsp;</TD>'.chr(13);
					else{
							$tablaipes=PintaOrdenadores($cmd,$valor);
							$HTMLparametros.=  '<TD style="BACKGROUND-COLOR: #b5daad;">&nbsp;'.$tablaipes.'&nbsp;</TD>'.chr(13);
					}
					$HTMLparametros.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #b5daad;" >&nbsp;</TH>'.chr(13);
					$HTMLparametros.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #b5daad;" >&nbsp;</TH>'.chr(13);
					$HTMLparametros.=  '</TR>'.chr(13);
				}
			}
		}
	}
	echo  $HTMLparametros;
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
