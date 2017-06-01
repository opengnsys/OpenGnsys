<?
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: Comando.php
// Descripción : 
//		Implementación del comando "EjecutarScripts"
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../idiomas/php/".$idioma."/comandos/enviarmensaje_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");
//________________________________________________________________________________________________________
include_once("./includes/capturaacciones.php");
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion);
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<LINK rel="stylesheet" type="text/css" href="../estilos.css">
	<SCRIPT language="javascript" src="./jscripts/EnviarMensaje.js"></SCRIPT>
	<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/validators.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/enviarmensaje_'.$idioma.'.js"></SCRIPT>'?>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<?
	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>';
	//________________________________________________________________________________________________________
	//
	include_once("./includes/FiltradoAmbito.php");
	//________________________________________________________________________________________________________
?>			
	<P align=center>
	<SPAN align=center class=subcabeceras><? echo $TbMsg[10] ?></SPAN>
	<form  align=center name="fdatos"> 				
		
		<table align=center  class=tabla_datos border="0" cellpadding="0" cellspacing="1">
			<tr> 
				<th>&nbsp;<? echo $TbMsg[7]?>&nbsp;</th>
				<td><input type="text" name="titulo"></td></tr>
			<tr> 
				<th>&nbsp;<? echo $TbMsg[9]?>&nbsp;</th>
				<td><textarea class="cajatexto" name="mensaje" cols="70" rows="18"></textarea></td></tr>
			<tr> <th align=center colspan="3"><? echo $TbMsg["OGAGENT"] ?></th></tr>
	    		<tr> <th align=center colspan="3"><? echo $TbMsg["OPTION"] ?></th></tr>
		</table>	
	</form>	
	<?
	//________________________________________________________________________________________________________
	include_once("./includes/formularioacciones.php");
	//________________________________________________________________________________________________________
	//  Sólo permite ejecutar inmediantemente como opción de ejecución 
	// include_once("./includes/opcionesacciones.php")
	?>
	<P align=center><span align=center class=subcabeceras>Opciones de Ejecución</span></P>
	<table align=center>
		<tr>
			<td><img border=0 style="cursor:pointer" src="../images/boton_aceptar_esp.gif" onclick="confirmar()" ></td>
		</tr>
	</table>
	<BR>
	<table class=opciones_ejecucion  align=center>
	    <tr>
		<td><input name=sw_ejya type=checkbox checked  readonly></td>
		<td colspan=3> Ejecutar inmediatamente &nbsp; </td>
	    </tr>
	    <tr>
		<td>&nbsp; </td>
		<td><input name=sw_seguimiento type=radio value=1  readonly></td><td>Incluirlo en Cola de Acciones&nbsp;</td>
	    </tr>
	    <tr>
		<td>&nbsp; </td>
		<td><input checked name=sw_seguimiento type=radio value=0  readonly></td><td>No incluirlo en Cola de Acciones&nbsp;</td>
	    </tr>
	</table>

	</FORM>
<SCRIPT language="javascript">
	Sondeo();
</SCRIPT>
</BODY>
</HTML>
