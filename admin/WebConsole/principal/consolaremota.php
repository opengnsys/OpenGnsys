<?
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

if (isset($_GET["idambito"])) $idambito=$_GET["idambito"]; 
if (isset($_GET["litambito"])) $litambito=$_GET["litambito"]; 
if (isset($_GET["nomambito"])) $nomambito=$_GET["nomambito"]; 
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
<SCRIPT language="javascript" src="../jscripts/ecoremoto.js"></SCRIPT>
<SCRIPT language="javascript" src="../jscripts/constantes.js"></SCRIPT>
<SCRIPT language="javascript">
	var vez=0;
//______________________________________________________________________________________________________
function PulsaEnter(oEvento){ 
    var iAscii; 
    if (oEvento.keyCode) 
        iAscii = oEvento.keyCode; 
    else{
		if (oEvento.which) 
			iAscii = oEvento.which; 
		else 
			return false; 
		}
    if (iAscii == 13) confirmar();
	return true; 
} 
//______________________________________________________________________________________________________
function confirmar(){ 
	var idambito=document.fdatos.idambito.value;
	var litambito=document.fdatos.litambito.value;

 	if(litambito==LITAMBITO_ORDENADORES){
		var diveco=document.getElementById("diveco");
		diveco.innerHTML="&nbsp";
	}
	var Obtcmd=document.getElementById("comando");
	var cmd=Obtcmd.value;

	var wurl="shellconsola.php";
	var prm="idambito="+idambito+"&litambito="+litambito+"&comando="+cmd;
	conmuta("visible");
	CallPage(wurl,prm,"resultado","POST");
}
//______________________________________________________________________________________________________
function resultado(iHTML){
	if(iHTML.length>0){
		var diveco=document.getElementById("diveco");
		diveco.innerHTML=iHTML
	}
	var litambito=document.fdatos.litambito.value;
 	if(litambito==LITAMBITO_ORDENADORES){
		if(vez==0){ // Activa el eco sólo la primera vez que se  envia comandos
			vez++;
			setTimeout("enviaping();",1000);
		} 
	}
	else{
		setTimeout('conmuta("hidden");',1000);
		//conmuta("hidden");
	}
}
//______________________________________________________________________________________________________
</SCRIPT> 
	<FORM name="fdatos" action="shellconsola.php">
		<INPUT type=hidden name="idambito" value=<?echo $idambito?>>
		<INPUT type=hidden name="litambito" value=<?echo $litambito?>>
		<INPUT type=hidden name="nomambito" value=<?echo $nomambito?>>
	</FORM>

<?
$cols="93";
$rows="30";
switch($litambito){
		case $LITAMBITO_CENTROS :
			$urlimg='../images/iconos/centros.gif';
			$textambito=$TbMsg[0];
			break;
		case $LITAMBITO_GRUPOSAULAS :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[1];
			break;
		case $LITAMBITO_AULAS :
			$urlimg='../images/iconos/aula.gif';
			$textambito=$TbMsg[2];
			break;
		case $LITAMBITO_GRUPOSORDENADORES :
			$urlimg='../images/iconos/carpeta.gif';
			$textambito=$TbMsg[3];
			break;
		case $LITAMBITO_ORDENADORES :
			$rows="3";
			$urlimg='../images/iconos/ordenador.gif';
			$textambito=$TbMsg[4];
			break;
	}
?>

	<P align=center class=cabeceras><?echo $TbMsg[7]?><BR>
	<SPAN align=center class=subcabeceras>
		<IMG src="<? echo $urlimg?>">&nbsp;<?echo $textambito.": ".$nomambito?></SPAN></P>
	<table align=center border="0" cellpadding="0" cellspacing="1">
			<tr><td  class="presentaciones"><? echo $TbMsg[11]?></td></tr>
			<tr><td ><textarea class="cajacomandos" name id="comando" cols="<? echo $cols?>" rows="<? echo $rows?>"></textarea></td></tr>
			<TR><TD align=center><A href=#><IMG border=0 src="../images/boton_confirmar.gif"  onclick="confirmar()"></A></TD></TR>
			<TR height=5><TD align=center>

		<?
				// Layer de las notificaciones de envío
				echo '<DIV id="layer_aviso" align=center style="visibility:hidden">';
				echo '<BR>';
				echo '<SPAN align=center class="marco">&nbsp;'.$TbMsg[14].'&nbsp;</SPAN>';
				echo '</DIV>';
				?>
			</TD></TR>
		<?
		if($litambito==$LITAMBITO_ORDENADORES){
			echo '<tr><td  class="presentaciones">'.$TbMsg[12].'</td></tr>';
			echo '<tr><td>';
			echo '<div id="diveco" class="marco" align=left style="width:700px;height:500px;overflow:scroll"></div>';
			echo '</td></tr>';
		}
		?>
	</table>
<?
	if($litambito!=$LITAMBITO_ORDENADORES){
		echo '<DIV id="Layer_nota" align=center>';
		echo '<BR>';
		echo '<SPAN align=center class=notas><I><b>'.$TbMsg[13].'</b></I></SPAN>';
		echo '</DIV>';
	}
?>

</BODY>
</HTML>
