<?  
// *************************************************************************************************************************************************
// Aplicación WEB: Hidra
// Copyright 2003-2005  José Manuel Alonso. Todos los derechos reservados.
// Fecha Creación: Año 2003-2004
// Fecha Última modificación: Marzo-2005
// Nombre del fichero: propiedades_servidoresdhcp.php
// Descripción : 
//		 Presenta el formulario de captura de datos de un servidor dhcp para insertar,modificar y eliminar
// *************************************************************************************************************************************************
include_once("../includes/ctrlacc.php");
include_once("../includes/opciones.php");
include_once("../includes/CreaComando.php");
include_once("../clases/AdoPhp.php");
include_once("../idiomas/php/".$idioma."/propiedades_servidoresdhcp_".$idioma.".php"); 
//________________________________________________________________________________________________________
$opcion=0;
$opciones=array($TbMsg[0],$TbMsg[1],$TbMsg[2],$TbMsg[3]);
//________________________________________________________________________________________________________
$idservidordhcp=0; 
$nombreservidordhcp="";
$ip="";
$grupoid=0;
$comentarios="";
$ordenadores=0; // Número de ordenador a los que da servicio

if (isset($_GET["opcion"])) $opcion=$_GET["opcion"]; // Recoge parametros
if (isset($_GET["idservidordhcp"])) $idservidordhcp=$_GET["idservidordhcp"]; 
if (isset($_GET["grupoid"])) $grupoid=$_GET["grupoid"]; 
if (isset($_GET["identificador"])) $idservidordhcp=$_GET["identificador"]; 
//________________________________________________________________________________________________________
$cmd=CreaComando($cadenaconexion); // Crea objeto comando
if (!$cmd)
	Header('Location: '.$pagerror.'?herror=2'); // Error de conexión con servidor B.D.
if  ($opcion!=$op_alta){
	$resul=TomaPropiedades($cmd,$idservidordhcp);
	if (!$resul)
		Header('Location: '.$pagerror.'?herror=3'); // Error de recuperación de datos.
}
//________________________________________________________________________________________________________
?>
<HTML>
<TITLE>Administración web de aulas</TITLE>
<HEAD>
	<LINK rel="stylesheet" type="text/css" href="../hidra.css">
	<SCRIPT language="javascript" src="../jscripts/propiedades_servidoresdhcp.js"></SCRIPT>
	<SCRIPT language="javascript" src="../jscripts/opciones.js"></SCRIPT>
	<? echo '<SCRIPT language="javascript" src="../idiomas/javascripts/'.$idioma.'/propiedades_servidoresdhcp_'.$idioma.'.js"></SCRIPT>'?>
</HEAD>
<BODY>
<DIV  align=center>
<FORM  name="fdatos"> 
	<INPUT type=hidden name=idservidordhcp value=<?=$idservidordhcp?>>
	<INPUT type=hidden name=grupoid value=<?=$grupoid?>>
	<INPUT type=hidden name=ordenadores value=<?=$ordenadores?>>
	<P align=center class=cabeceras><?echo $TbMsg[4]?><BR>
	<SPAN align=center class=subcabeceras><? echo $opciones[$opcion]?></SPAN></P>
	<TABLE  align=center border=0 cellPadding=1 cellSpacing=1 class=tabla_datos >
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[5]?>&nbsp;</TD>
			<?
				if ($opcion==$op_eliminacion)
					echo '<TD>'.$nombreservidordhcp.'</TD>';
				else	
					echo '<TD><INPUT  class="formulariodatos" name=nombreservidordhcp style="width:200" type=text value="'.$nombreservidordhcp.'"></TD>';
			?>
			<TD colspan=2 valign=top align=left rowspan=3><CENTER><IMG border=3 style="border-color:#63676b" src="../images/aula.jpg"><BR>&nbsp;Ordenadores:&nbsp;<? echo $ordenadores?></CENTER></TD>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[6]?>&nbsp;</TD>
			<?
			if ($opcion==$op_eliminacion)
					echo '<TD>'.$ip.'</TD>';
			else	
				echo'<TD><INPUT  class="formulariodatos" name=ip type=text style="width:200" value="'.$ip.'"></TD>';
			?>
		</TR>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
		<TR>
			<TH align=center>&nbsp;<?echo $TbMsg[7]?>&nbsp;</TD>
			<?
			if ($opcion==$op_eliminacion)
					echo '<TD>'.$comentarios.'</TD>';
			else	
				echo '<TD><TEXTAREA   class="formulariodatos" name=comentarios rows=2 cols=50>'.$comentarios.'</TEXTAREA></TD>';
			?>
		</TR>	
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------->
	</TABLE>
</FORM>
</DIV>
<?
//________________________________________________________________________________________________________
include_once("../includes/opcionesbotonesop.php");
//________________________________________________________________________________________________________
//________________________________________________________________________________________________________
include_once("../includes/iframecomun.php");
//________________________________________________________________________________________________________
?>
</BODY>
</HTML>
<?
//________________________________________________________________________________________________________
//	Recupera los datos de un servidor dhcp
//		Parametros: 
//		- cmd: Una comando ya operativo (con conexión abierta)  
//		- id: El identificador del servidor
//________________________________________________________________________________________________________
function TomaPropiedades($cmd,$id){
	global $nombreservidordhcp;
	global $ip;
	global $comentarios;
	global $ordenadores;

	$rs=new Recordset; 
	$cmd->texto="SELECT * FROM servidoresdhcp WHERE idservidordhcp=".$id;
	$rs->Comando=&$cmd; 
	if (!$rs->Abrir()) return(0); // Error al abrir recordset
	$rs->Primero(); 
	if (!$rs->EOF){
		$nombreservidordhcp=$rs->campos["nombreservidordhcp"];
		$ip=$rs->campos["ip"];
		$comentarios=$rs->campos["comentarios"];
		$rs->Cerrar();
		$cmd->texto="SELECT count(*) as numordenadores FROM ordenadores WHERE idservidordhcp=".$id;
		$rs->Comando=&$cmd; 
		if (!$rs->Abrir()) return(0); // Error al abrir recordset
		$rs->Primero(); 
		if (!$rs->EOF)
			$ordenadores=$rs->campos["numordenadores"];
		$rs->Cerrar();
		return(true);
	}
	else
		return(false);
}
?>
