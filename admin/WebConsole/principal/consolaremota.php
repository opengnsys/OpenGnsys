<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2010
// Fecha Última modificación: Marzo-2010
// Nombre del fichero: consolaremota.php
// Descripción : 
//		Crea una consola remota para escribir comandos de la shell de forma remota
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/CreaComando.php");
include_once("../idiomas/php/".$idioma."/consolaremota_".$idioma.".php");
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//___________________________________________________________________________________________________
$idambito="";
$litambito="";
$nomambito=""; 
$ambito="";
$sw=0; // Controla priemas y segundas llamadas al cliente (ejecución previa o sólo eco)

if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if (isset($_GET["litambito"])) $litambito=$_GET["litambito"]; 
if (isset($_GET["nomambito"])) $nomambito=$_GET["nomambito"]; 
if (isset($_GET["sw"])) $sw=$_GET["sw"]; 

/* Tamaño del textarea de código */
$cols="95";
$rows="10";	
switch($litambito){
		case $LITAMBITO_CENTROS :
			$urlimg='../images/iconos/centros.gif';
			$textambito=$TbMsg[0];
			$ambito=$AMBITO_CENTROS;
			break;
		case $LITAMBITO_GRUPOSAULAS :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[1];
			$ambito=$AMBITO_GRUPOSAULAS;
			break;
		case $LITAMBITO_AULAS :
			$urlimg='../images/iconos/aula.gif';
			$textambito=$TbMsg[2];
			$ambito=$AMBITO_AULAS;
			break;
		case $LITAMBITO_GRUPOSORDENADORES :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[3];
			$ambito=$AMBITO_GRUPOSORDENADORES;
			break;
		case $LITAMBITO_ORDENADORES :
			$urlimg='../images/iconos/ordenador.gif';
			$textambito=$TbMsg[4];
			$ambito=$AMBITO_ORDENADORES;
			break;
	}
	
//___________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<LINK rel="stylesheet" type="text/css" href="../estilos.css">
</HEAD>
<BODY>
<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/comunes.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/consolaremota.js"></SCRIPT>
<?php echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/consolaremota_'.$idioma.'.js"></SCRIPT>'?>

	<FORM name="fdatos">
		<INPUT type=hidden name="idambito" value=<?php echo $idambito?>>
		<INPUT type=hidden name="litambito" value=<?php echo $litambito?>>
		<INPUT type=hidden name="ambito" value=<?php echo $ambito?>>
		<INPUT type=hidden name="nomambito" value=<?php echo $nomambito?>>
	</FORM>

	<P align=center class=cabeceras><?php echo $TbMsg[7]?><BR>
	<SPAN align=center class=subcabeceras>
		<IMG src="<?php echo $urlimg?>">&nbsp;<?php echo $textambito.": ".$nomambito?></SPAN></P>
	<TABLE align=center border="0" cellpadding="0" cellspacing="1">
			<TR>
				<TD align=center class="presentaciones"><?php echo $TbMsg[11]?></TD></TR>
				
			<?php
			if($sw==1){ // caja para código del script			
				echo '<TR>
						<TD align=center><textarea onfocus="conmuta(\'hidden\');" class="cajacomandos" name id="comando" cols="'.$cols.'"
						rows="'.$rows.'"></textarea></TD></TR>
					<TR>
						<TD align=center>
							<BR><IMG border=0 style="cursor:pointer" src="../images/boton_confirmar_'.$idioma.'.gif"
							onclick="confirmar()"></TD></TR>';
			}
			if($ambito==$AMBITO_ORDENADORES) //Mensaje de espera
				$msg=$TbMsg[14];
			else
				$msg=$TbMsg[15];
				echo '<TR><TD align=center>';
				// Layer de las notificaciones de envío 
				echo '<DIV  id="layer_aviso" align=center style="visibility:hidden">';
				echo '<BR>';
				echo '<SPAN align=center class="marco">&nbsp;'.$msg.'&nbsp;</SPAN>';
				echo '</DIV>';
				echo '</TD></TR>';
			if($ambito==$AMBITO_ORDENADORES){ // Nota al pie				
				echo '<TR>
						<TD   align=center class="presentaciones">'.$TbMsg[12].'</TD></TR>';
				echo '<TR>
						<TD align=center>';
				echo '		<div id="diveco" class="marco" align=left style="width:700px;height:400px;overflow:scroll"></div>';
				echo '	</TD>
					</TR>';
			}
			?>
	</TABLE>
<?php
	if($ambito!=$AMBITO_ORDENADORES){ // Nota al pie
		echo '<DIV id="Layer_nota" align=center>';
		echo '<BR>';
		echo '<SPAN align=center class=notas><I><b>'.$TbMsg[13].'</b></I></SPAN>';
		echo '</DIV>';

	}
?>
<SCRIPT language="javascript">
	<?php
		if($sw==2){ // Llama a eco
			echo 'sw=2;';
			echo 'enviaMsg();';
		}
	?>	
</SCRIPT>

</BODY>
</HTML>
