<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: tareascomandos.php
// Descripción : 
//		Administra los comandos que forman parte de una tarea
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/TomaDato.php");
include_once("../idiomas/php/".$idioma."/tareascomandos_".$idioma.".php");
//________________________________________________________________________________________________________

$idtarea=0; 
$descripciontarea=""; 
if (isset($_GET["idtarea"])) $idtarea=$_GET["idtarea"]; // Recoge parametros
if (isset($_GET["descripciontarea"])) $descripciontarea=$_GET["descripciontarea"]; // Recoge parametros

$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
<SCRIPT language="javascript" src="../jscripts/tareascomandos.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/tareascomandos_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<FORM  name="fdatos"> 
	<INPUT type=hidden value="<?php echo $idcentro?>" id=idcentro>	 
	<P align=center class=cabeceras><IMG src="../images/iconos/tareas.gif">&nbsp;<?php echo $TbMsg[0]?><BR>
	<SPAN align=center class=subcabeceras><?php echo $TbMsg[1]?></SPAN>&nbsp;<IMG src="../images/iconos/comandos.gif"><BR><BR>
	<SPAN align=center class=presentaciones><B><?php echo $TbMsg[2]?>:&nbsp;</B><?php echo $descripciontarea?></SPAN></P>
	<DIV align=center id="Layer_componentes">
		<TABLE class="tabla_listados" cellspacing=1 cellpadding=0 id="TABLACOMANDOS">
				<TR height=20>
					<TH><IMG src="../images/iconos/eliminar.gif"></TH>
					<TH align=left>&nbsp;<?php echo $TbMsg[3]?></TH>
					<TH><?php echo $TbMsg[4]?></TH>
					<TH>A</TH>
			</TR>
		<?php
			$rs=new Recordset; 
			$cmd->texto='SELECT  tareas_acciones.*, comandos.descripcion,comandos.visuparametros 
							FROM tareas_acciones 
							INNER JOIN procedimientos ON tareas_acciones.idprocedimiento = procedimientos.idprocedimiento 
							INNER JOIN procedimientos_acciones ON procedimientos.idprocedimiento = procedimientos_acciones.idprocedimiento 
							INNER JOIN comandos ON procedimientos_acciones.idcomando = comandos.idcomando 
							WHERE tareas_acciones.idtarea='.$idtarea.' ORDER BY tareas_acciones.orden';
			$rs->Comando=&$cmd; 
			if ($rs->Abrir()){ 
				$rs->Primero();
				$tabla_parametros=""; // Tabla  para localizar parametros
				$cont_parametros=0; // Contador de la tabla 
				CreaTablaParametros($cmd); // Crea tabla  especificaciones de parametros
				while (!$rs->EOF){
						 echo '<TR id=TR-'.$rs->campos["idtareacomando"].'>';
						 echo '<TD align=center ><INPUT  id=checkbox-'.$rs->campos["idtareacomando"].' type=checkbox	
						 onclick="gestion_comandos('.$rs->campos["idtareacomando"].',this)" checked ></INPUT></TD>';
						// Descripcion de la comando
						 echo '<TD>&nbsp;'.$rs->campos["descripcion"].'</TD>';
						// Orden del item del item
						echo '<TD align=center >&nbsp;<INPUT class="formulariodatos" id=orden-'.$rs->campos["idtareacomando"].' 
						style="WIDTH:20px" type=text value="'.$rs->campos["orden"].'"></INPUT></TD>';
						echo '<TD width="10%" align=center id="imgact-'.$rs->campos["idtareacomando"].'">
						<IMG src="../images/iconos/actualizar.gif" style="cursor:hand" onclick="ActualizarAccion('.$rs->campos["idtareacomando"].')"></TD>';
						echo '</TR>';
						pintacomandos($cmd,$rs);
					echo  '<TR height=3><TD style="BACKGROUND-COLOR: #999999;" colspan=5></TD></TR>'.chr(13);
						$rs->Siguiente();
				}
			}
			$rs->Cerrar();
		?>
		</TABLE>
	</DIV>		
	<DIV id="Layer_nota"  align=center>
		<br>
		<span align=left class=notas><I><?php echo $TbMsg[7]?>.</I></span>
	</DIV>
</FORM>
</BODY>
</HTML>
<?php
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

	$HTMLparametros='<TR  id="PAR-'.$rs->campos["idtareacomando"].'">'.chr(13);
	$HTMLparametros.= '<TD>&nbsp;</TD>'.chr(13);
	$HTMLparametros.= '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4; " >'.$TbMsg[5].'</TH>'.chr(13);
	$HTMLparametros.= '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4;" >'.$TbMsg[6].'</TH>'.chr(13);
	$HTMLparametros.= '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #D4D4D4;" >&nbsp;</TH>'.chr(13);
	$HTMLparametros.= '</TR>'.chr(13);

	$textambito="";
	$urlimg="";
	$nombre="";
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
	$HTMLparametros.='<TR  id="PAR-'.$rs->campos["idtareacomando"].'">'.chr(13);
	$HTMLparametros.= '<TD>&nbsp;</TD>'.chr(13);
	$HTMLparametros.= '<TD style="BACKGROUND-COLOR: #b5daad;">&nbsp;'.$TbMsg[8].'&nbsp;</TD>'.chr(13);
	$HTMLparametros.= '<TD style="BACKGROUND-COLOR: #b5daad;">&nbsp;'.$textambito.'&nbsp;';
	$HTMLparametros.= '<IMG src="'.$urlimg.'">&nbsp;</TD>'.chr(13);
	$HTMLparametros.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #b5daad;" >&nbsp;</TH>'.chr(13);
	$HTMLparametros.=	'</TR>';

	$HTMLparametros.='<TR  id="PAR-'.$rs->campos["idtareacomando"].'">'.chr(13);
	$HTMLparametros.= '<TD>&nbsp;</TD>'.chr(13);
	$HTMLparametros.= '<TD style="BACKGROUND-COLOR: #b5daad;">&nbsp;'.$TbMsg[9].'&nbsp;</TD>'.chr(13);
	$HTMLparametros.= '<TD style="BACKGROUND-COLOR: #b5daad;">&nbsp;'.$nombre.'&nbsp;</TD>'.chr(13);
	$HTMLparametros.=  '<TH style="FONT-WEIGHT: 700;COLOR: #000000;BACKGROUND-COLOR: #b5daad;" >&nbsp;</TH>'.chr(13);
	$HTMLparametros.=	'</TR>';

	$auxVP=explode(";",$rs->campos["visuparametros"]); // Parametros visualizables
	$auxP=explode(chr(13),$rs->campos["parametros"]); // Recorre parametros para visualizar los que así sean
	for ($i=0;$i<sizeof($auxP);$i++){
		$dualparam=explode("=",$auxP[$i]);
		for ($k=0;$k<sizeof($auxVP);$k++){
			 if($auxVP[$k]==$dualparam[0]){
				$posp=busca_indicebinariodual($dualparam[0],$tabla_parametros,$cont_parametros); // Busca datos del parámetro en la tabla cargada previamentre con todos los parámetros
				if ($posp>=0){
					$auxtabla_parametros=$tabla_parametros[$posp][1];
					$HTMLparametros.='<TR  id="PAR-'.$rs->campos["idtareacomando"].'">'.chr(13);
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
					$HTMLparametros.=  '</TR>'.chr(13);
				}
			}
		}
	}
	echo  $HTMLparametros;
}
//________________________________________________________________________________________________________
function PintaOrdenadores($cmd,$cadenaip){
	$auxP=explode(";",$cadenaip); 
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
		$tablaHtml.= '<TD align=center style="BACKGROUND-COLOR: #b5daad;FONT-FAMILY: Arial, Helvetica, sans-serif;	BORDER-BOTTOM:#000000 none;FONT-SIZE: 8px">
					<IMG src="../images/iconos/ordenador.gif"><br><span style="FONT-SIZE:9px" >'.$rs->campos["nombreordenador"].'</TD>';
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
