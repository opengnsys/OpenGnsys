<?php
// *************************************************************************************************************************************************
// Aplicación WEB: ogAdmWebCon
// Autor: José Manuel Alonso (E.T.S.I.I.) Universidad de Sevilla
// Fecha Creación: Año 2009-2010
// Fecha Última modificación: Agosto-2010
// Nombre del fichero: Arrancar.php
// Descripción : 
//		Implementación del comando "Arrancar"
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../clases/AdoPhp.php");
include_once("../includes/constantes.php");
include_once("../includes/comunes.php");
include_once("../includes/CreaComando.php");
include_once("../includes/HTMLSELECT.php");
include_once("../idiomas/php/".$idioma."/comandos/arrancar_".$idioma.".php");
include_once("../idiomas/php/".$idioma."/comandos/opcionesacciones_".$idioma.".php");
//________________________________________________________________________________________________________
//
include_once("./includes/capturaacciones.php");
//________________________________________________________________________________________________________
//
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
	<SCRIPT language="javascript" src="./jscripts/Arrancar.js"></SCRIPT>
	<SCRIPT language="javascript" src="./jscripts/comunescomandos.js"></SCRIPT>
	<SCRIPT language="javascript" src="../clases/jscripts/HttpLib.js"></SCRIPT>		
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/comandos/comunescomandos_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<?php
	echo '<p align=center><span class=cabeceras>'.$TbMsg[5].'&nbsp;</span><br>';
	//________________________________________________________________________________________________________
	//
	include_once("./includes/FiltradoAmbito.php");
?>
	<P align=center><SPAN align=center class=subcabeceras>Datos suministrar<? echo $TbMsg[7] ?></SPAN></p>
	</BR>
	<TABLE id="tabla_conf" align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos>
		<TR>
			<TH align=center>&nbsp;&nbsp;</Th>
			<TH align=center>&nbsp;<?php echo $TbMsg[10] ?>&nbsp;</TH>
		</TR>
		<TR>
			<TD align=center><input type="radio" checked id="broadcast" name="metodo" value="1"></TD>
			<TD align=center>&nbsp;<?php echo $TbMsg[8] ?>&nbsp;</TD>
		</TR>
		<TR>
			<TD align=center><input type="radio" id="unicast" name="metodo" value="2"></TD>	
			<TD align=center>&nbsp;<?php echo $TbMsg[9] ?>&nbsp;</TD>
			
		</TR>
	</TABLE>
<?php
	//________________________________________________________________________________________________________
	//
	include_once("./includes/formularioacciones.php");
	//________________________________________________________________________________________________________
	//
	include_once("./includes/opcionesacciones.php");
	//_______________________________________________________________________________________________________	
	
?>
<SCRIPT language="javascript">
	Sondeo();
</SCRIPT>
</BODY>
</HTML>

